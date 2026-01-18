<?php

class AjusteInventarioModel {

    private $db;

    private string $T_AJUSTE = 'inventario_ajuste';
    private string $T_DET    = 'inventario_ajuste_detalle';
    private string $T_MOVS   = 'inventario_movimientos';
    private string $T_ART    = 'articulos';

    public function __construct() {
        $this->db = Database::connect();
    }

    public function crear(array $cabecera, array $detalle, $usuario_id): array {

        if (empty($detalle)) {
            throw new Exception('No hay artículos para ajustar');
        }

        $fecha = trim($cabecera['fecha'] ?? '');
        if ($fecha === '') {
            $fecha = date('Y-m-d H:i:s');
        } else {
            $fecha = str_replace('T', ' ', $fecha);
            if (strlen($fecha) === 16) $fecha .= ':00';
        }

        $notas = trim($cabecera['notas'] ?? '');
        $notas = ($notas !== '') ? $notas : null;

        // ===== precargar costos desde artículos =====
        $ids = [];
        foreach ($detalle as $it) {
            $id = (int)($it['articulo_id'] ?? 0);
            if ($id > 0) $ids[] = $id;
        }
        $ids = array_values(array_unique($ids));
        if (empty($ids)) throw new Exception('Detalle inválido');

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $stCostos = $this->db->prepare("
            SELECT id, precio_compra
            FROM {$this->T_ART}
            WHERE id IN ($placeholders)
        ");
        $stCostos->execute($ids);

        $mapCostos = [];
        while ($row = $stCostos->fetch(PDO::FETCH_ASSOC)) {
            $mapCostos[(int)$row['id']] = (float)($row['precio_compra'] ?? 0);
        }

        $this->db->beginTransaction();

        try {
            // 1) cabecera
            $st = $this->db->prepare("
                INSERT INTO {$this->T_AJUSTE} (fecha, usuario_id, notas)
                VALUES (:fecha, :usuario_id, :notas)
            ");
            $st->execute([
                ':fecha'      => $fecha,
                ':usuario_id' => $usuario_id,
                ':notas'      => $notas
            ]);

            $ajusteId = (int)$this->db->lastInsertId();

            // 2) detalle
            $stDet = $this->db->prepare("
                INSERT INTO {$this->T_DET}
                (inventario_ajuste_id, articulo_id, stock_actual, stock_fisico, ajuste, costo)
                VALUES (:aj, :art, :act, :fis, :dif, :costo)
            ");

            // 3) movimientos
            // OJO: tu enum(origen) permite: COMPRA, VENTA, AJUSTE, TRASPASO
            $stMov = $this->db->prepare("
                INSERT INTO {$this->T_MOVS} (articulo_id, tipo, origen, referencia_id, cantidad, costo)
                VALUES (:articulo_id, :tipo, 'AJUSTE', :ref, :cantidad, :costo)
            ");

            // 4) actualizar stock en artículos (mantener sincronizado)
            $stUpd = $this->db->prepare("
                UPDATE {$this->T_ART}
                SET stock = :stock
                WHERE id = :id
            ");

            foreach ($detalle as $it) {
                $articulo_id  = (int)($it['articulo_id'] ?? 0);
                $stock_actual = (float)($it['stock_actual'] ?? 0);
                $stock_fisico = (float)($it['stock_fisico'] ?? 0);

                if ($articulo_id <= 0) continue;

                $dif = round($stock_fisico - $stock_actual, 2);

                // si no hay cambio, no guardamos nada
                if (abs($dif) < 0.00001) continue;

                $costo = (float)($mapCostos[$articulo_id] ?? 0);

                $stDet->execute([
                    ':aj'    => $ajusteId,
                    ':art'   => $articulo_id,
                    ':act'   => round($stock_actual, 2),
                    ':fis'   => round($stock_fisico, 2),
                    ':dif'   => $dif,
                    ':costo' => round($costo, 2)
                ]);

                $tipo = ($dif > 0) ? 'ENTRADA' : 'SALIDA';

                $stMov->execute([
                    ':articulo_id' => $articulo_id,
                    ':tipo'        => $tipo,
                    ':ref'         => $ajusteId,
                    ':cantidad'    => round(abs($dif), 2),
                    ':costo'       => round($costo, 2)
                ]);

                // stock queda igual al físico
                $stUpd->execute([
                    ':stock' => round($stock_fisico, 2),
                    ':id'    => $articulo_id
                ]);
            }

            $this->db->commit();
            return ['id' => $ajusteId];

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * ✅ LISTADO PARA AJUSTE
     * - "descripcion" NO existe en articulos, es "nombre"
     * - stock REAL se calcula desde inventario_movimientos (como Ventas)
     * - si no hay movimientos, stock = 0
     */
    public function listarArticulos(string $q = ''): array {

        $q = trim($q);

        $sql = "
            SELECT
                a.id,
                a.codigo,
                a.nombre AS descripcion,
                a.precio_compra,
                COALESCE(SUM(
                    CASE
                        WHEN m.tipo = 'ENTRADA' THEN m.cantidad
                        WHEN m.tipo = 'SALIDA'  THEN -m.cantidad
                        ELSE 0
                    END
                ), 0) AS stock
            FROM {$this->T_ART} a
            LEFT JOIN {$this->T_MOVS} m
                ON m.articulo_id = a.id
            WHERE a.activo = 1
        ";

        $params = [];

        if ($q !== '') {
            $sql .= " AND (a.codigo LIKE :q OR a.nombre LIKE :q) ";
            $params[':q'] = "%{$q}%";
        }

        $sql .= "
            GROUP BY a.id, a.codigo, a.nombre, a.precio_compra
            ORDER BY a.nombre ASC
            LIMIT 500
        ";

        $st = $this->db->prepare($sql);
        $st->execute($params);

        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
