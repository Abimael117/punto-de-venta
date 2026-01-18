<?php

class CuentasPagarModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    private string $T_CXP     = 'cuentas_pagar';
    private string $T_PAGOS   = 'cuentas_pagar_pagos';
    private string $T_PROV    = 'proveedores';
    private string $T_COMPRAS = 'compras';

    // Lista: PENDIENTE | PAGADA | TODOS
    public function listar(string $estado = 'PENDIENTE'): array {

        $where = "";
        if ($estado === 'PENDIENTE' || $estado === 'PAGADA' || $estado === 'CANCELADA' || $estado === 'VENCIDA') {
            $where = "WHERE cxp.estatus = :estado";
        }

        $sql = "
          SELECT
            cxp.id,
            cxp.compra_id,
            cxp.proveedor_id,
            COALESCE(pv.nombre, 'Proveedor') AS proveedor_nombre,
            cxp.concepto,
            cxp.fecha,
            cxp.vence,
            cxp.estatus,
            cxp.total,
            cxp.saldo,
            cxp.created_at,

            COALESCE((
              SELECT SUM(pg.monto)
              FROM {$this->T_PAGOS} pg
              WHERE pg.cuenta_pagar_id = cxp.id
            ), 0) AS pagado

          FROM {$this->T_CXP} cxp
          LEFT JOIN {$this->T_PROV} pv ON pv.id = cxp.proveedor_id
          $where
          ORDER BY cxp.id DESC
        ";

        $st = $this->db->prepare($sql);
        if ($where) $st->bindValue(':estado', $estado);
        $st->execute();

        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getCuenta(int $cuenta_id): ?array {
        $sql = "
          SELECT
            cxp.*,
            COALESCE(pv.nombre, 'Proveedor') AS proveedor_nombre,
            COALESCE((
              SELECT SUM(pg.monto)
              FROM {$this->T_PAGOS} pg
              WHERE pg.cuenta_pagar_id = cxp.id
            ), 0) AS pagado
          FROM {$this->T_CXP} cxp
          LEFT JOIN {$this->T_PROV} pv ON pv.id = cxp.proveedor_id
          WHERE cxp.id = :id
          LIMIT 1
        ";

        $st = $this->db->prepare($sql);
        $st->bindValue(':id', $cuenta_id);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function listarPagos(int $cuenta_id): array {
        $sql = "
          SELECT id, cuenta_pagar_id, monto, metodo, referencia, created_at
          FROM {$this->T_PAGOS}
          WHERE cuenta_pagar_id = :id
          ORDER BY id DESC
        ";
        $st = $this->db->prepare($sql);
        $st->bindValue(':id', $cuenta_id);
        $st->execute();

        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Inserta pago y actualiza saldo/estatus (transacción + lock)
    public function registrarPago(int $cuenta_id, $usuario_id, float $monto, string $metodo, ?string $referencia): int {

        $this->db->beginTransaction();

        // Lock de la cuenta para evitar pagos simultáneos
        $st = $this->db->prepare("
          SELECT id, total, saldo, estatus
          FROM {$this->T_CXP}
          WHERE id = :id
          FOR UPDATE
        ");
        $st->bindValue(':id', $cuenta_id);
        $st->execute();

        $cxp = $st->fetch(PDO::FETCH_ASSOC);

        if (!$cxp) {
            $this->db->rollBack();
            throw new Exception("Cuenta no encontrada");
        }

        $saldo = floatval($cxp['saldo']);
        $estatus = strtoupper($cxp['estatus'] ?? 'PENDIENTE');

        if ($estatus !== 'PENDIENTE' || $saldo <= 0) {
            $this->db->rollBack();
            throw new Exception("Cuenta no pendiente");
        }

        if ($monto > $saldo) {
            $this->db->rollBack();
            throw new Exception("Monto mayor al saldo");
        }

        // Insert pago
        $ins = $this->db->prepare("
          INSERT INTO {$this->T_PAGOS} (cuenta_pagar_id, fecha, metodo, referencia, monto, desde_caja, caja_mov_id, notas, created_at)
          VALUES (:cuenta_id, NOW(), :metodo, :referencia, :monto, 0, NULL, NULL, CURRENT_TIMESTAMP)
        ");
        $ins->bindValue(':cuenta_id', $cuenta_id);
        $ins->bindValue(':metodo', $metodo);
        $ins->bindValue(':referencia', $referencia);
        $ins->bindValue(':monto', round($monto, 2));
        $ins->execute();

        $idPago = intval($this->db->lastInsertId());

        // Actualiza saldo + estatus
        $nuevoSaldo = round($saldo - $monto, 2);
        $nuevoEstatus = ($nuevoSaldo <= 0.0001) ? 'PAGADA' : 'PENDIENTE';

        $up = $this->db->prepare("
          UPDATE {$this->T_CXP}
          SET saldo = :saldo, estatus = :estatus
          WHERE id = :id
        ");
        $up->bindValue(':saldo', $nuevoSaldo);
        $up->bindValue(':estatus', $nuevoEstatus);
        $up->bindValue(':id', $cuenta_id);
        $up->execute();

        $this->db->commit();
        return $idPago;
    }
}
