<?php

require_once __DIR__ . '/InventarioMovimientosModel.php';

class ComprasModel {

    private $db;
    private $inventario;

    private array $colsCompras = [];

    public function __construct() {
        $this->db = Database::connect();
        $this->inventario = new InventarioMovimientosModel();
        $this->colsCompras = $this->getColumns('compras');
    }

    private function getColumns(string $table): array {
        $st = $this->db->prepare("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :t
        ");
        $st->bindValue(':t', $table);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];
        return array_flip($rows); // para isset rápido
    }

    private function hasCol(string $col): bool {
        return isset($this->colsCompras[$col]);
    }

    public function generarFolio(): string {
        return 'CMP-' . date('Ymd-His');
    }

    /**
     * Crea cuenta por pagar ligada a la compra (si no existe)
     * Requiere: cuentas_pagar ya creada
     */
    private function crearCxPDesdeCompra(array $compra, int $compraId, string $folio): void {

        // 1) Evitar duplicados por compra_id
        $chk = $this->db->prepare("SELECT id FROM cuentas_pagar WHERE compra_id = :cid LIMIT 1");
        $chk->execute([':cid' => $compraId]);
        if ($chk->fetch(PDO::FETCH_ASSOC)) return;

        $proveedorId = (int)($compra['proveedor_id'] ?? 0);
        if ($proveedorId <= 0) {
            // Si no hay proveedor, no tiene sentido generar CxP
            return;
        }

        // fecha base: usa fecha_hora si viene, si no, ahora
        $fechaHora = $compra['fecha_hora'] ?? date('Y-m-d H:i:s');
        $fecha = substr((string)$fechaHora, 0, 10); // YYYY-MM-DD

        // vencimiento default: +30 días (ajústalo si luego agregas días crédito)
        $vence = date('Y-m-d', strtotime($fecha . ' +30 days'));

        $notas = trim((string)($compra['notas'] ?? ''));
        $concepto = $notas !== '' ? $notas : ("Compra a crédito " . $folio);

        $total = round((float)($compra['total'] ?? 0), 2);
        if ($total <= 0) return;

        $ins = $this->db->prepare("
            INSERT INTO cuentas_pagar
            (proveedor_id, compra_id, folio, concepto, fecha, vence, total, saldo, estatus, notas)
            VALUES
            (:proveedor_id, :compra_id, :folio, :concepto, :fecha, :vence, :total, :saldo, 'PENDIENTE', :notas)
        ");

        $ins->execute([
            ':proveedor_id' => $proveedorId,
            ':compra_id'    => $compraId,
            ':folio'        => $folio,
            ':concepto'     => $concepto,
            ':fecha'        => $fecha,
            ':vence'        => $vence,
            ':total'        => $total,
            ':saldo'        => $total,
            ':notas'        => ($notas !== '' ? $notas : null)
        ]);
    }

    /**
     * @return array ['id'=>int, 'folio'=>string]
     */
    public function crearCompra($compra, $detalle): array {

        $this->db->beginTransaction();

        try {

            if (empty($detalle)) {
                throw new Exception('Sin detalle');
            }

            $folio = $this->generarFolio();

            // -------------------------
            // 1) INSERT COMPRA (cabecera)
            // -------------------------
            $cols = ['folio', 'proveedor_id', 'subtotal', 'impuestos_total', 'total'];
            $vals = [':folio', ':proveedor_id', ':subtotal', ':impuestos_total', ':total'];

            // extras opcionales (solo si existen en tabla)
            $extraMap = [
                'fecha_hora'       => ':fecha_hora',
                'tipo'             => ':tipo',
                'metodo_pago'      => ':metodo_pago',
                'pagada_con_caja'  => ':pagada_con_caja',
                'notas'            => ':notas',
            ];

            foreach ($extraMap as $col => $param) {
                if ($this->hasCol($col)) {
                    $cols[] = $col;
                    $vals[] = $param;
                }
            }

            $sqlCompra = "
                INSERT INTO compras (" . implode(',', $cols) . ")
                VALUES (" . implode(',', $vals) . ")
            ";

            $stmt = $this->db->prepare($sqlCompra);

            $params = [
                ':folio'           => $folio,
                ':proveedor_id'    => $compra['proveedor_id'] ?? null,
                ':subtotal'        => (float)($compra['subtotal'] ?? 0),
                ':impuestos_total' => (float)($compra['impuestos_total'] ?? 0),
                ':total'           => (float)($compra['total'] ?? 0),
            ];

            if ($this->hasCol('fecha_hora')) {
                $params[':fecha_hora'] = !empty($compra['fecha_hora']) ? $compra['fecha_hora'] : null;
            }

            // Normalizamos tipo/método
            $tipoNormalizado = 'CONTADO';
            if ($this->hasCol('tipo')) {
                $tipo = strtoupper(trim($compra['tipo'] ?? 'CONTADO'));
                $tipoNormalizado = in_array($tipo, ['CONTADO','CREDITO'], true) ? $tipo : 'CONTADO';
                $params[':tipo'] = $tipoNormalizado;
            } else {
                // por si no existe col tipo, aún así lo ocupamos para CxP
                $tipo = strtoupper(trim($compra['tipo'] ?? 'CONTADO'));
                $tipoNormalizado = in_array($tipo, ['CONTADO','CREDITO'], true) ? $tipo : 'CONTADO';
            }

            if ($this->hasCol('metodo_pago')) {
                $met = strtoupper(trim($compra['metodo_pago'] ?? 'EFECTIVO'));
                $params[':metodo_pago'] = in_array($met, ['EFECTIVO','TARJETA','TRANSFERENCIA'], true) ? $met : 'EFECTIVO';
            }

            if ($this->hasCol('pagada_con_caja')) {
                $params[':pagada_con_caja'] = !empty($compra['pagada_con_caja']) ? 1 : 0;
            }

            if ($this->hasCol('notas')) {
                $notas = trim($compra['notas'] ?? '');
                $params[':notas'] = $notas !== '' ? $notas : null;
            }

            $stmt->execute($params);

            $compraId = (int)$this->db->lastInsertId();

            // ✅ 1.1) SI ES CRÉDITO => CREAR CUENTA POR PAGAR
            if ($tipoNormalizado === 'CREDITO') {
                $this->crearCxPDesdeCompra((array)$compra, $compraId, $folio);
            }

            // -------------------------
            // 2) PREPARE DETALLE (una vez)
            // -------------------------
            $stmtDet = $this->db->prepare("
                INSERT INTO compras_detalle
                (compra_id, articulo_id, impuesto_id, impuesto_tasa, impuesto_monto,
                 cantidad, costo, costo_con_impuesto, utilidad_pct,
                 precio_venta_sugerido, precio_venta, importe)
                VALUES
                (:compra_id, :articulo_id, :impuesto_id, :impuesto_tasa, :impuesto_monto,
                 :cantidad, :costo, :costo_con_impuesto, :utilidad_pct,
                 :precio_venta_sugerido, :precio_venta, :importe)
            ");

            // ✅ update de precios en artículos
            $stmtUpdArticulo = $this->db->prepare("
                UPDATE articulos
                SET precio_compra = :precio_compra,
                    precio_venta  = :precio_venta
                WHERE id = :id
            ");

            foreach ($detalle as $item) {

                $articuloId = (int)($item['articulo_id'] ?? 0);
                $cantidad   = (float)($item['cantidad'] ?? 0);
                $costo      = (float)($item['costo'] ?? 0);

                if ($articuloId <= 0 || $cantidad <= 0) continue;

                $impuestoId    = !empty($item['impuesto_id']) ? (int)$item['impuesto_id'] : null;
                $impuestoTasa  = (float)($item['impuesto_tasa'] ?? 0);
                $impuestoMonto = (float)($item['impuesto_monto'] ?? 0);

                $costoConImp   = (float)($item['costo_con_impuesto'] ?? 0);
                $utilidadPct   = (float)($item['utilidad_pct'] ?? 0);

                $pvSug         = (float)($item['precio_venta_sugerido'] ?? 0);
                $pv            = (float)($item['precio_venta'] ?? 0);

                $importe = isset($item['importe'])
                    ? (float)$item['importe']
                    : (float)(($cantidad * $costo) + $impuestoMonto);

                $stmtDet->execute([
                    ':compra_id'             => $compraId,
                    ':articulo_id'           => $articuloId,
                    ':impuesto_id'           => $impuestoId,
                    ':impuesto_tasa'         => $impuestoTasa,
                    ':impuesto_monto'        => $impuestoMonto,
                    ':cantidad'              => $cantidad,
                    ':costo'                 => $costo,
                    ':costo_con_impuesto'    => $costoConImp,
                    ':utilidad_pct'          => $utilidadPct,
                    ':precio_venta_sugerido' => $pvSug,
                    ':precio_venta'          => $pv,
                    ':importe'               => $importe,
                ]);

                // ✅ ENTRADA INVENTARIO
                $this->inventario->entrada(
                    $articuloId,
                    'COMPRA',
                    $compraId,
                    $cantidad,
                    $costo
                );

                // ✅ Actualizar precios
                $stmtUpdArticulo->execute([
                    ':precio_compra' => $costo,
                    ':precio_venta'  => $pv,
                    ':id'            => $articuloId
                ]);
            }

            $this->db->commit();

            return ['id' => $compraId, 'folio' => $folio];

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
