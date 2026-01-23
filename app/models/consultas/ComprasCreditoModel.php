<?php

class ComprasCreditoModel {

    private $db;

    private array $colsCxp = [];
    private bool $hasPagosTable = false;
    private array $colsPagos = [];

    public function __construct() {
        $this->db = Database::connect();
        $this->colsCxp = $this->getColumns('cuentas_pagar');

        $this->hasPagosTable = $this->tableExists('cuentas_pagar_pagos');
        if ($this->hasPagosTable) {
            $this->colsPagos = $this->getColumns('cuentas_pagar_pagos');
        }
    }

    private function tableExists(string $table): bool {
        $st = $this->db->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :t
        ");
        $st->execute([':t' => $table]);
        return (int)$st->fetchColumn() > 0;
    }

    private function getColumns(string $table): array {
        $st = $this->db->prepare("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :t
        ");
        $st->execute([':t' => $table]);
        $rows = $st->fetchAll(PDO::FETCH_COLUMN) ?: [];
        return array_flip($rows);
    }

    private function hasCol(array $cols, string $col): bool {
        return isset($cols[$col]);
    }

    private function pagosFkCol(): ?string {
        if (!$this->hasPagosTable) return null;
        if ($this->hasCol($this->colsPagos, 'cuenta_pagar_id')) return 'cuenta_pagar_id';
        if ($this->hasCol($this->colsPagos, 'cxp_id')) return 'cxp_id'; // compat viejo
        return null;
    }

    private function pagosCajaFlagCol(): ?string {
        if (!$this->hasPagosTable) return null;
        if ($this->hasCol($this->colsPagos, 'desde_caja')) return 'desde_caja';     // TU BD
        if ($this->hasCol($this->colsPagos, 'afecta_caja')) return 'afecta_caja';   // compat
        return null;
    }

    private function buildFilters(array $f, array &$params): string {

        $where = [];

        if (!empty($f['proveedor_id'])) {
            $where[] = "cxp.proveedor_id = :proveedor_id";
            $params[':proveedor_id'] = (int)$f['proveedor_id'];
        }

        if (!empty($f['estatus'])) {
            $where[] = "cxp.estatus = :estatus";
            $params[':estatus'] = strtoupper(trim($f['estatus']));
        }

        if (!empty($f['q'])) {
            $where[] = "(cxp.folio LIKE :q OR cxp.concepto LIKE :q OR p.nombre LIKE :q)";
            $params[':q'] = '%' . $f['q'] . '%';
        }

        if ($this->hasCol($this->colsCxp, 'fecha')) {
            if (!empty($f['desde'])) {
                $where[] = "cxp.fecha >= :desde";
                $params[':desde'] = $f['desde'];
            }
            if (!empty($f['hasta'])) {
                $where[] = "cxp.fecha <= :hasta";
                $params[':hasta'] = $f['hasta'];
            }
        }

        return $where ? ("WHERE " . implode(" AND ", $where)) : "";
    }

    public function listar(array $f): array {

        $params = [];
        $where = $this->buildFilters($f, $params);

        $select = "
            cxp.id,
            cxp.proveedor_id,
            p.nombre AS proveedor,
            cxp.compra_id,
            cxp.folio,
            cxp.concepto,
            " . ($this->hasCol($this->colsCxp, 'fecha') ? "cxp.fecha" : "NULL AS fecha") . ",
            " . ($this->hasCol($this->colsCxp, 'vence') ? "cxp.vence" : "NULL AS vence") . ",
            cxp.total,
            cxp.saldo,
            cxp.estatus,
            " . ($this->hasCol($this->colsCxp, 'notas') ? "cxp.notas" : "NULL AS notas") . "
        ";

        $sql = "
            SELECT {$select}
            FROM cuentas_pagar cxp
            INNER JOIN proveedores p ON p.id = cxp.proveedor_id
            {$where}
            ORDER BY
              (CASE WHEN cxp.estatus='PENDIENTE' THEN 0 ELSE 1 END),
              " . ($this->hasCol($this->colsCxp, 'vence') ? "cxp.vence" : "cxp.id") . " ASC,
              cxp.id DESC
            LIMIT 500
        ";

        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function resumen(array $f): array {

        $params = [];
        $where = $this->buildFilters($f, $params);

        $sql = "
            SELECT
              COUNT(*) AS docs,
              COALESCE(SUM(cxp.total),0) AS total,
              COALESCE(SUM(cxp.saldo),0) AS saldo,
              COALESCE(SUM(CASE WHEN cxp.estatus='PENDIENTE' THEN cxp.saldo ELSE 0 END),0) AS saldo_pendiente
            FROM cuentas_pagar cxp
            INNER JOIN proveedores p ON p.id = cxp.proveedor_id
            {$where}
        ";

        $st = $this->db->prepare($sql);
        $st->execute($params);
        $r = $st->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'docs'            => (int)($r['docs'] ?? 0),
            'total'           => (float)($r['total'] ?? 0),
            'saldo'           => (float)($r['saldo'] ?? 0),
            'saldo_pendiente' => (float)($r['saldo_pendiente'] ?? 0),
        ];
    }

    public function getDetalle(int $cxpId): array {

        $st = $this->db->prepare("
            SELECT cxp.*, p.nombre AS proveedor
            FROM cuentas_pagar cxp
            INNER JOIN proveedores p ON p.id = cxp.proveedor_id
            WHERE cxp.id = :id
            LIMIT 1
        ");
        $st->execute([':id' => $cxpId]);
        $cxp = $st->fetch(PDO::FETCH_ASSOC);
        if (!$cxp) throw new Exception('No existe la cuenta por pagar');

        $compra = null;
        $detalle = [];

        $compraId = (int)($cxp['compra_id'] ?? 0);
        if ($compraId > 0) {
            $stC = $this->db->prepare("
                SELECT c.*
                FROM compras c
                WHERE c.id = :id
                LIMIT 1
            ");
            $stC->execute([':id' => $compraId]);
            $compra = $stC->fetch(PDO::FETCH_ASSOC) ?: null;

            $stD = $this->db->prepare("
                SELECT d.*, a.codigo, a.descripcion
                FROM compras_detalle d
                INNER JOIN articulos a ON a.id = d.articulo_id
                WHERE d.compra_id = :id
                ORDER BY d.id ASC
            ");
            $stD->execute([':id' => $compraId]);
            $detalle = $stD->fetchAll(PDO::FETCH_ASSOC) ?: [];
        }

        $pagos = [];
        if ($this->hasPagosTable) {
            $fk = $this->pagosFkCol();
            if ($fk) {
                $stP = $this->db->prepare("
                    SELECT *
                    FROM cuentas_pagar_pagos
                    WHERE {$fk} = :id
                    ORDER BY id DESC
                ");
                $stP->execute([':id' => $cxpId]);
                $pagos = $stP->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }
        }

        return [
            'cxp'           => $cxp,
            'compra'        => $compra,
            'detalle'       => $detalle,
            'pagos'         => $pagos,
            'pagos_enabled' => $this->hasPagosTable
        ];
    }

    public function abonar(array $data): array {

        // compat: cxp_id o cuenta_pagar_id
        $cxpId = (int)($data['cxp_id'] ?? ($data['cuenta_pagar_id'] ?? 0));
        $monto = (float)($data['monto'] ?? 0);

        if ($cxpId <= 0) throw new Exception('Cuenta inválida');
        if ($monto <= 0) throw new Exception('Monto inválido');

        $metodo = strtoupper(trim((string)($data['metodo'] ?? 'EFECTIVO')));
        $allowed = ['EFECTIVO','TARJETA','TRANSFERENCIA','CHEQUE','OTRO'];
        if (!in_array($metodo, $allowed, true)) $metodo = 'EFECTIVO';

        $fecha = trim((string)($data['fecha'] ?? ''));
        if ($fecha === '') $fecha = date('Y-m-d');

        $referencia = trim((string)($data['referencia'] ?? ''));
        $notas = trim((string)($data['notas'] ?? ''));

        $this->db->beginTransaction();

        try {

            $st = $this->db->prepare("SELECT id, saldo, total, estatus FROM cuentas_pagar WHERE id=:id LIMIT 1 FOR UPDATE");
            $st->execute([':id' => $cxpId]);
            $cxp = $st->fetch(PDO::FETCH_ASSOC);
            if (!$cxp) throw new Exception('No existe la cuenta por pagar');

            $saldo = (float)($cxp['saldo'] ?? 0);
            if ($saldo <= 0) throw new Exception('La cuenta ya está liquidada');
            if ($monto > $saldo) $monto = $saldo;

            // 1) Insert pago si hay tabla
            if ($this->hasPagosTable) {

                $fk = $this->pagosFkCol();
                if (!$fk) throw new Exception('La tabla de pagos no tiene FK válida (cuenta_pagar_id/cxp_id)');

                $cols = [$fk, 'fecha', 'monto', 'metodo'];
                $vals = [':fk', ':fecha', ':monto', ':metodo'];

                $params = [
                    ':fk'     => $cxpId,
                    ':fecha'  => $fecha,
                    ':monto'  => $monto,
                    ':metodo' => $metodo,
                ];

                if ($this->hasCol($this->colsPagos, 'referencia')) {
                    $cols[] = 'referencia'; $vals[] = ':referencia';
                    $params[':referencia'] = ($referencia !== '' ? $referencia : null);
                }

                if ($this->hasCol($this->colsPagos, 'notas')) {
                    $cols[] = 'notas'; $vals[] = ':notas';
                    $params[':notas'] = ($notas !== '' ? $notas : null);
                }

                // checkbox caja: TU BD usa desde_caja (no afecta_caja)
                $flagCol = $this->pagosCajaFlagCol();
                if ($flagCol) {
                    $cols[] = $flagCol; $vals[] = ':flag';
                    $params[':flag'] = !empty($data['afecta_caja']) ? 1 : 0;
                }

                $sql = "INSERT INTO cuentas_pagar_pagos (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
                $ins = $this->db->prepare($sql);
                $ins->execute($params);
            }

            // 2) Actualizar saldo/estatus
            $nuevoSaldo = round($saldo - $monto, 2);
            $nuevoEstatus = $nuevoSaldo <= 0 ? 'PAGADA' : 'PENDIENTE';

            $up = $this->db->prepare("UPDATE cuentas_pagar SET saldo=:s, estatus=:e WHERE id=:id");
            $up->execute([':s' => $nuevoSaldo, ':e' => $nuevoEstatus, ':id' => $cxpId]);

            $this->db->commit();

            return [
                'cxp_id'        => $cxpId,
                'monto'         => $monto,
                'saldo'         => $nuevoSaldo,
                'estatus'       => $nuevoEstatus,
                'pagos_enabled' => $this->hasPagosTable
            ];

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function marcarPagada(int $cxpId): void {
        if ($cxpId <= 0) throw new Exception('ID inválido');
        $st = $this->db->prepare("UPDATE cuentas_pagar SET saldo=0, estatus='PAGADA' WHERE id=:id");
        $st->execute([':id' => $cxpId]);
    }
}
