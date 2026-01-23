<?php

class VentasCreditoModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * LISTADO TOTALIZADO POR CLIENTE
     */
    public function listarClientes(array $f) : array {

        $desde  = trim($f['desde'] ?? '');
        $hasta  = trim($f['hasta'] ?? '');
        $estado = trim($f['estado'] ?? 'all');
        $q      = trim($f['q'] ?? '');

        $where  = [];
        $params = [];

        // Solo créditos pendientes con saldo
        $where[] = "c.saldo > 0";
        $where[] = "c.estado = 'PENDIENTE'";

        if ($desde !== '') {
            $where[] = "DATE(v.created_at) >= :desde";
            $params[':desde'] = $desde;
        }
        if ($hasta !== '') {
            $where[] = "DATE(v.created_at) <= :hasta";
            $params[':hasta'] = $hasta;
        }

        // Estado por vencimiento (al menos uno del grupo cae en esa condición)
        // Para filtrar por grupo usamos HAVING, pero aquí lo hacemos con WHERE sobre cada crédito.
        // (si filtras "vencidos", solo cuenta créditos vencidos; el total por cliente será de esos).
        if ($estado === 'vencidos') {
            $where[] = "c.fecha_vencimiento < CURDATE()";
        } elseif ($estado === 'porvencer') {
            $where[] = "c.fecha_vencimiento >= CURDATE() AND c.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
        } elseif ($estado === 'aldia') {
            $where[] = "c.fecha_vencimiento > DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
        }

        if ($q !== '') {
            $where[] = "(cli.nombre LIKE :q OR cli.codigo LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        $sql = "
            SELECT
                cli.id      AS cliente_id,
                cli.codigo  AS cliente_codigo,
                cli.nombre  AS cliente_nombre,

                COUNT(c.id)         AS n_creditos,
                SUM(c.total)        AS credito_total,
                SUM(c.saldo)        AS saldo_total,

                MIN(c.fecha_vencimiento) AS proximo_vencimiento,
                MIN(DATEDIFF(c.fecha_vencimiento, CURDATE())) AS dias_restantes_min,

                CASE
                    WHEN SUM(c.fecha_vencimiento < CURDATE()) > 0 THEN 'VENCIDO'
                    WHEN SUM(c.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)) > 0 THEN 'POR_VENCER'
                    ELSE 'AL_DIA'
                END AS semaforo
            FROM creditos c
            INNER JOIN ventas v     ON v.id = c.venta_id
            INNER JOIN clientes cli ON cli.id = c.cliente_id
        ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= "
            GROUP BY cli.id, cli.codigo, cli.nombre
            ORDER BY
                (CASE
                    WHEN semaforo = 'VENCIDO' THEN 1
                    WHEN semaforo = 'POR_VENCER' THEN 2
                    ELSE 3
                END) ASC,
                proximo_vencimiento ASC,
                saldo_total DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * DETALLE POR CLIENTE: regresa
     * - cliente (totales)
     * - creditos (lista de créditos/ventas)
     */
    public function getDetalleCliente(int $clienteId, array $f = []) : ?array {

        $desde  = trim($f['desde'] ?? '');
        $hasta  = trim($f['hasta'] ?? '');
        $estado = trim($f['estado'] ?? 'all');
        $q      = trim($f['q'] ?? '');

        $where  = [];
        $params = [':cliente_id' => $clienteId];

        $where[] = "c.cliente_id = :cliente_id";
        $where[] = "c.saldo > 0";
        $where[] = "c.estado = 'PENDIENTE'";

        if ($desde !== '') {
            $where[] = "DATE(v.created_at) >= :desde";
            $params[':desde'] = $desde;
        }
        if ($hasta !== '') {
            $where[] = "DATE(v.created_at) <= :hasta";
            $params[':hasta'] = $hasta;
        }

        if ($estado === 'vencidos') {
            $where[] = "c.fecha_vencimiento < CURDATE()";
        } elseif ($estado === 'porvencer') {
            $where[] = "c.fecha_vencimiento >= CURDATE() AND c.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
        } elseif ($estado === 'aldia') {
            $where[] = "c.fecha_vencimiento > DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
        }

        // Búsqueda: folio o algo del cliente (por si entras desde búsqueda general)
        if ($q !== '') {
            $where[] = "(v.folio LIKE :q OR cli.nombre LIKE :q OR cli.codigo LIKE :q)";
            $params[':q'] = '%' . $q . '%';
        }

        // Cliente
        $stmtC = $this->db->prepare("
            SELECT id AS cliente_id, codigo AS cliente_codigo, nombre AS cliente_nombre
            FROM clientes
            WHERE id = :id
            LIMIT 1
        ");
        $stmtC->execute([':id' => $clienteId]);
        $cliente = $stmtC->fetch(PDO::FETCH_ASSOC);
        if (!$cliente) return null;

        // Créditos del cliente
        $sql = "
            SELECT
                c.id                AS credito_id,
                c.venta_id          AS venta_id,
                v.folio             AS folio,
                v.created_at        AS fecha,
                v.total             AS venta_total,
                c.total             AS credito_total,
                c.saldo             AS saldo,
                c.dias_credito      AS dias_credito,
                c.fecha_vencimiento AS fecha_vencimiento,

                CASE
                    WHEN c.fecha_vencimiento < CURDATE() THEN 'VENCIDO'
                    WHEN c.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) THEN 'POR_VENCER'
                    ELSE 'AL_DIA'
                END AS semaforo,

                DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias_restantes
            FROM creditos c
            INNER JOIN ventas v     ON v.id = c.venta_id
            INNER JOIN clientes cli ON cli.id = c.cliente_id
        ";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY v.created_at DESC, c.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $creditos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$creditos) return null;

        // Totales del cliente (en base al detalle filtrado)
        $credito_total = 0;
        $saldo_total = 0;
        $proximo_vto = null;
        $semaforo = 'AL_DIA';
        $dias_min = null;

        foreach ($creditos as $r) {
            $credito_total += (float)($r['credito_total'] ?? 0);
            $saldo_total   += (float)($r['saldo'] ?? 0);

            $fv = $r['fecha_vencimiento'] ?? null;
            if ($fv && ($proximo_vto === null || $fv < $proximo_vto)) $proximo_vto = $fv;

            $d = (int)($r['dias_restantes'] ?? 0);
            if ($dias_min === null || $d < $dias_min) $dias_min = $d;

            if (($r['semaforo'] ?? '') === 'VENCIDO') $semaforo = 'VENCIDO';
            elseif ($semaforo !== 'VENCIDO' && ($r['semaforo'] ?? '') === 'POR_VENCER') $semaforo = 'POR_VENCER';
        }

        return [
            'cliente' => [
                'cliente_id'           => $cliente['cliente_id'],
                'cliente_codigo'       => $cliente['cliente_codigo'],
                'cliente_nombre'       => $cliente['cliente_nombre'],
                'n_creditos'           => count($creditos),
                'credito_total'        => $credito_total,
                'saldo_total'          => $saldo_total,
                'proximo_vencimiento'  => $proximo_vto,
                'dias_restantes_min'   => $dias_min,
                'semaforo'             => $semaforo,
            ],
            'creditos' => $creditos
        ];
    }

    /**
     * DETALLE DE UN CRÉDITO + PAGOS
     */
    public function getDetalleCredito(int $creditoId) : ?array {

        $stmt = $this->db->prepare("
            SELECT
                c.id                AS credito_id,
                c.venta_id          AS venta_id,
                v.folio             AS folio,
                v.created_at        AS fecha,
                cli.id              AS cliente_id,
                cli.codigo          AS cliente_codigo,
                cli.nombre          AS cliente_nombre,
                v.total             AS venta_total,
                c.total             AS credito_total,
                c.saldo             AS saldo,
                c.dias_credito      AS dias_credito,
                c.fecha_vencimiento AS fecha_vencimiento,
                c.estado            AS estado,
                DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias_restantes
            FROM creditos c
            INNER JOIN ventas v     ON v.id = c.venta_id
            INNER JOIN clientes cli ON cli.id = c.cliente_id
            WHERE c.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $creditoId]);
        $credito = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$credito) return null;

        $pagos = [];
        try {
            $stmt2 = $this->db->prepare("
                SELECT
                    id,
                    credito_id,
                    monto,
                    metodo,
                    referencia,
                    created_at
                FROM creditos_pagos
                WHERE credito_id = :id
                ORDER BY created_at DESC, id DESC
            ");
            $stmt2->execute([':id' => $creditoId]);
            $pagos = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $pagos = [];
        }

        return [
            'credito' => $credito,
            'pagos'   => $pagos
        ];
    }
}
