<?php

class InventarioInicialModel {

    private $db;

    private string $T_INVINI = 'inventario_inicial';
    private string $T_DET    = 'inventario_inicial_detalle';
    private string $T_MOVS   = 'inventario_movimientos';

    // 👇 Asumido por tu JS: /operaciones/articulos/buscar devuelve precio_compra
    private string $T_ART    = 'articulos';

    public function __construct() {
        $this->db = Database::connect();
    }

    public function crear(array $cabecera, array $detalle, $usuario_id): array {

        if (empty($detalle)) {
            throw new Exception('No hay artículos en el inventario inicial');
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

        // ===== precargar costos desde artículos (una sola consulta) =====
        $ids = [];
        foreach ($detalle as $it) {
            $id = (int)($it['articulo_id'] ?? 0);
            if ($id > 0) $ids[] = $id;
        }
        $ids = array_values(array_unique($ids));

        if (empty($ids)) {
            throw new Exception('Detalle inválido');
        }

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
                INSERT INTO {$this->T_INVINI} (fecha, usuario_id, notas)
                VALUES (:fecha, :usuario_id, :notas)
            ");
            $st->execute([
                ':fecha'      => $fecha,
                ':usuario_id' => $usuario_id,
                ':notas'      => $notas
            ]);

            $invId = (int)$this->db->lastInsertId();

            // 2) detalle (costo viene de artículos)
            $stDet = $this->db->prepare("
                INSERT INTO {$this->T_DET} (inventario_inicial_id, articulo_id, cantidad, costo)
                VALUES (:inv, :art, :cant, :costo)
            ");

            // 3) movimientos reales
            $stMov = $this->db->prepare("
                INSERT INTO {$this->T_MOVS} (articulo_id, tipo, origen, referencia_id, cantidad, costo)
                VALUES (:articulo_id, 'ENTRADA', 'INVENTARIO_INICIAL', :ref, :cantidad, :costo)
            ");

            foreach ($detalle as $it) {
                $articulo_id = (int)($it['articulo_id'] ?? 0);
                $cantidad    = (float)($it['cantidad'] ?? 0);

                if ($articulo_id <= 0) continue;
                if ($cantidad <= 0) continue;

                $costo = (float)($mapCostos[$articulo_id] ?? 0);

                $stDet->execute([
                    ':inv'   => $invId,
                    ':art'   => $articulo_id,
                    ':cant'  => round($cantidad, 2),
                    ':costo' => round($costo, 2)
                ]);

                $stMov->execute([
                    ':articulo_id' => $articulo_id,
                    ':ref'         => $invId,
                    ':cantidad'    => round($cantidad, 2),
                    ':costo'       => round($costo, 2)
                ]);
            }

            $this->db->commit();
            return ['id' => $invId];

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
