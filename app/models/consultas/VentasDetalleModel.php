<?php

class VentasDetalleModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    private function normalizarFecha($s): ?string {
        $s = trim((string)$s);
        if ($s === '') return null;

        // acepta YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;

        return null;
    }

    public function listar(array $f): array {

        $desde   = $this->normalizarFecha($f['desde'] ?? '');
        $hasta   = $this->normalizarFecha($f['hasta'] ?? '');
        $credito = (string)($f['credito'] ?? 'all');
        $q       = trim((string)($f['q'] ?? ''));

        $where  = [];
        $params = [];

        // Rango de fechas sobre ventas.created_at
        if ($desde) {
            $where[] = "v.created_at >= :desde";
            $params[':desde'] = $desde . " 00:00:00";
        }
        if ($hasta) {
            $where[] = "v.created_at <= :hasta";
            $params[':hasta'] = $hasta . " 23:59:59";
        }

        // Crédito: all | 1 | 0
        if ($credito === '1' || $credito === '0') {
            $where[] = "v.es_credito = :credito";
            $params[':credito'] = (int)$credito;
        }

        // Búsqueda general: folio, cliente, código cliente, artículo
        if ($q !== '') {
            $where[] = "(
                v.folio LIKE :q
                OR c.nombre LIKE :q
                OR c.codigo LIKE :q
                OR a.nombre LIKE :q
                OR a.codigo LIKE :q
            )";
            $params[':q'] = '%' . $q . '%';
        }

        $sqlWhere = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        $sql = "
            SELECT
                v.id            AS venta_id,
                v.folio         AS folio,
                v.created_at    AS fecha,
                v.total         AS total_venta,
                v.es_credito    AS es_credito,

                c.id            AS cliente_id,
                c.codigo        AS cliente_codigo,
                c.nombre        AS cliente_nombre,

                vd.id           AS detalle_id,
                vd.articulo_id  AS articulo_id,
                vd.cantidad     AS cantidad,
                vd.precio       AS precio,
                vd.importe      AS importe,

                a.codigo        AS articulo_codigo,
                a.nombre        AS articulo_nombre

            FROM ventas v
            INNER JOIN ventas_detalle vd ON vd.venta_id = v.id
            LEFT JOIN clientes c         ON c.id = v.cliente_id
            LEFT JOIN articulos a        ON a.id = vd.articulo_id

            $sqlWhere

            ORDER BY v.id DESC, vd.id ASC
            LIMIT 800
        ";

        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDetalleVenta(int $ventaId): array {

        $sql = "
            SELECT
                v.id          AS venta_id,
                v.folio       AS folio,
                v.created_at  AS fecha,
                v.total       AS total_venta,
                v.es_credito  AS es_credito,

                c.codigo      AS cliente_codigo,
                c.nombre      AS cliente_nombre,

                vd.id         AS detalle_id,
                vd.articulo_id,
                vd.cantidad,
                vd.precio,
                vd.importe,

                a.codigo      AS articulo_codigo,
                a.nombre      AS articulo_nombre

            FROM ventas v
            INNER JOIN ventas_detalle vd ON vd.venta_id = v.id
            LEFT JOIN clientes c         ON c.id = v.cliente_id
            LEFT JOIN articulos a        ON a.id = vd.articulo_id

            WHERE v.id = :id
            ORDER BY vd.id ASC
        ";

        $st = $this->db->prepare($sql);
        $st->execute([':id' => $ventaId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) return [];

        // Empaquetado bonito
        $head = [
            'venta_id'        => (int)$rows[0]['venta_id'],
            'folio'           => $rows[0]['folio'],
            'fecha'           => $rows[0]['fecha'],
            'total_venta'     => (float)$rows[0]['total_venta'],
            'es_credito'      => (int)$rows[0]['es_credito'],
            'cliente_codigo'  => $rows[0]['cliente_codigo'],
            'cliente_nombre'  => $rows[0]['cliente_nombre'],
        ];

        $items = [];
        foreach ($rows as $r) {
            $items[] = [
                'detalle_id'      => (int)$r['detalle_id'],
                'articulo_id'     => (int)$r['articulo_id'],
                'articulo_codigo' => $r['articulo_codigo'],
                'articulo_nombre' => $r['articulo_nombre'],
                'cantidad'        => (float)$r['cantidad'],
                'precio'          => (float)$r['precio'],
                'importe'         => (float)$r['importe'],
            ];
        }

        return [
            'venta'  => $head,
            'items'  => $items
        ];
    }
}
