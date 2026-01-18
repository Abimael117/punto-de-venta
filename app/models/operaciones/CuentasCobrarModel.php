<?php

class CuentasCobrarModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    private string $T_CREDITOS = 'creditos';
    private string $T_PAGOS    = 'creditos_pagos';
    private string $T_CLIENTES = 'clientes';
    private string $T_VENTAS   = 'ventas';

    // Lista créditos: PENDIENTE | PAGADO | TODOS
    public function listar(string $estado = 'PENDIENTE'): array {

        $where = "";
        if ($estado === 'PENDIENTE' || $estado === 'PAGADO' || $estado === 'CANCELADO') {
            $where = "WHERE cr.estado = :estado";
        }

        $sql = "
          SELECT
            cr.id,
            cr.venta_id,
            cr.cliente_id,
            COALESCE(cl.nombre, 'Cliente') AS cliente_nombre,
            cr.total,
            cr.saldo,
            cr.dias_credito,
            cr.fecha_vencimiento,
            cr.estado,
            cr.created_at,

            COALESCE((
              SELECT SUM(p.monto)
              FROM {$this->T_PAGOS} p
              WHERE p.credito_id = cr.id
            ), 0) AS pagado

          FROM {$this->T_CREDITOS} cr
          LEFT JOIN {$this->T_CLIENTES} cl ON cl.id = cr.cliente_id
          $where
          ORDER BY cr.id DESC
        ";

        $st = $this->db->prepare($sql);

        if ($where) $st->bindValue(':estado', $estado);

        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getCredito(int $credito_id): ?array {
        $sql = "
          SELECT
            cr.*,
            COALESCE(cl.nombre, 'Cliente') AS cliente_nombre,
            COALESCE((
              SELECT SUM(p.monto)
              FROM {$this->T_PAGOS} p
              WHERE p.credito_id = cr.id
            ), 0) AS pagado
          FROM {$this->T_CREDITOS} cr
          LEFT JOIN {$this->T_CLIENTES} cl ON cl.id = cr.cliente_id
          WHERE cr.id = :id
          LIMIT 1
        ";

        $st = $this->db->prepare($sql);
        $st->bindValue(':id', $credito_id);
        $st->execute();

        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function listarPagos(int $credito_id): array {
        $sql = "
          SELECT id, credito_id, usuario_id, monto, metodo, referencia, created_at
          FROM {$this->T_PAGOS}
          WHERE credito_id = :id
          ORDER BY id DESC
        ";
        $st = $this->db->prepare($sql);
        $st->bindValue(':id', $credito_id);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Inserta pago y actualiza saldo/estado del crédito (todo en transacción)
    public function registrarPago(int $credito_id, $usuario_id, float $monto, string $metodo, ?string $referencia): int {

        $this->db->beginTransaction();

        // Bloqueo del crédito para evitar pagos simultáneos
        $st = $this->db->prepare("SELECT id, total, saldo, estado FROM {$this->T_CREDITOS} WHERE id = :id FOR UPDATE");
        $st->bindValue(':id', $credito_id);
        $st->execute();
        $cr = $st->fetch(PDO::FETCH_ASSOC);

        if (!$cr) {
            $this->db->rollBack();
            throw new Exception("Crédito no encontrado");
        }

        $saldo = floatval($cr['saldo']);
        $estado = strtoupper($cr['estado'] ?? 'PENDIENTE');

        if ($estado !== 'PENDIENTE' || $saldo <= 0) {
            $this->db->rollBack();
            throw new Exception("Crédito no pendiente");
        }

        if ($monto > $saldo) {
            $this->db->rollBack();
            throw new Exception("Monto mayor al saldo");
        }

        // Insert pago
        $ins = $this->db->prepare("
          INSERT INTO {$this->T_PAGOS} (credito_id, usuario_id, monto, metodo, referencia)
          VALUES (:credito_id, :usuario_id, :monto, :metodo, :referencia)
        ");
        $ins->bindValue(':credito_id', $credito_id);
        $ins->bindValue(':usuario_id', $usuario_id);
        $ins->bindValue(':monto', round($monto, 2));
        $ins->bindValue(':metodo', $metodo);
        $ins->bindValue(':referencia', $referencia);
        $ins->execute();

        $idPago = intval($this->db->lastInsertId());

        // Actualiza saldo + estado
        $nuevoSaldo = round($saldo - $monto, 2);
        $nuevoEstado = ($nuevoSaldo <= 0.0001) ? 'PAGADO' : 'PENDIENTE';

        $up = $this->db->prepare("
          UPDATE {$this->T_CREDITOS}
          SET saldo = :saldo, estado = :estado
          WHERE id = :id
        ");
        $up->bindValue(':saldo', $nuevoSaldo);
        $up->bindValue(':estado', $nuevoEstado);
        $up->bindValue(':id', $credito_id);
        $up->execute();

        $this->db->commit();
        return $idPago;
    }
}
