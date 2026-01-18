<?php

class DashboardModel {

    private $db; // PDO

    public function __construct() {
        $this->db = Database::connect();
    }

    /* =========================================================
       GRAFICA DE VENTAS
    ========================================================= */

    public function getVentasChart(string $mode): array {
        return match ($mode) {
            'week'  => $this->ventasUltimos7Dias(),
            'month' => $this->ventasMesActual(),
            default => $this->ventasUltimos12Meses(),
        };
    }

    private function ventasUltimos7Dias(): array {
        $sql = "
            SELECT DATE(created_at) fecha, SUM(total) total
            FROM ventas
            WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(created_at)
            ORDER BY fecha
        ";

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $map  = array_column($rows, 'total', 'fecha');

        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $d = new DateTime("-$i day");
            $key = $d->format('Y-m-d');
            $labels[] = $this->diaES($d->format('N'));
            $values[] = (float)($map[$key] ?? 0);
        }

        return [
            'title'  => 'Últimos 7 días',
            'labels' => $labels,
            'values' => $values
        ];
    }

    private function ventasMesActual(): array {
        $sql = "
            SELECT DAY(created_at) dia, SUM(total) total
            FROM ventas
            WHERE YEAR(created_at)=YEAR(CURDATE())
              AND MONTH(created_at)=MONTH(CURDATE())
            GROUP BY dia
            ORDER BY dia
        ";

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $map  = array_column($rows, 'total', 'dia');

        $diasMes = (int)date('t');
        $labels = [];
        $values = [];

        for ($d = 1; $d <= $diasMes; $d++) {
            $labels[] = (string)$d;
            $values[] = (float)($map[$d] ?? 0);
        }

        return [
            'title'  => 'Mes actual (día a día)',
            'labels' => $labels,
            'values' => $values
        ];
    }

    private function ventasUltimos12Meses(): array {
        $sql = "
            SELECT DATE_FORMAT(created_at,'%Y-%m') ym, SUM(total) total
            FROM ventas
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
            GROUP BY ym
            ORDER BY ym
        ";

        $rows = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $map  = array_column($rows, 'total', 'ym');

        $labels = [];
        $values = [];

        $d = new DateTime(date('Y-m-01'));
        $d->modify('-11 months');

        for ($i = 0; $i < 12; $i++) {
            $key = $d->format('Y-m');
            $labels[] = $this->mesES($d->format('n'));
            $values[] = (float)($map[$key] ?? 0);
            $d->modify('+1 month');
        }

        return [
            'title'  => 'Últimos 12 meses',
            'labels' => $labels,
            'values' => $values
        ];
    }

    /* =========================================================
       ALERTAS INTELIGENTES
    ========================================================= */

    // 🔴 Productos con stock bajo (STOCK REAL)
    public function getAlertStockBajo(int $limit = 5): array {

        // ✅ Stock real = sum(ENTRADA) - sum(SALIDA)
        // Nota: si tu sistema usa otro valor para SALIDA (ej: 'VENTA'), dime y lo ajusto.
        $sql = "
            SELECT
                a.id,
                a.nombre,
                COALESCE(
                    SUM(
                        CASE
                            WHEN m.tipo = 'ENTRADA' THEN m.cantidad
                            WHEN m.tipo = 'SALIDA'  THEN -m.cantidad
                            ELSE 0
                        END
                    ), 0
                ) AS stock_real
            FROM articulos a
            LEFT JOIN inventario_movimientos m
                ON m.articulo_id = a.id
            WHERE a.activo = 1
            GROUP BY a.id, a.nombre
            HAVING stock_real <= 5
            ORDER BY stock_real ASC
            LIMIT :lim
        ";

        $st = $this->db->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();

        $out = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $stock = (float)($r['stock_real'] ?? 0);

            $out[] = [
                'name'      => $r['nombre'],
                'meta'      => 'Stock actual: '.number_format($stock, 2),
                'pill'      => ($stock <= 0) ? 'Agotado' : 'Bajo',
                'pillClass' => ($stock <= 0) ? 'danger' : 'warn'
            ];
        }

        return $out;
    }

    // 🔴 Créditos vencidos
    public function getAlertCxcVencidas(int $limit = 5): array {
        $sql = "
            SELECT c.nombre, cr.saldo, cr.fecha_vencimiento
            FROM creditos cr
            JOIN clientes c ON c.id = cr.cliente_id
            WHERE cr.estado = 'PENDIENTE'
              AND cr.fecha_vencimiento < CURDATE()
            ORDER BY cr.fecha_vencimiento ASC
            LIMIT :lim
        ";

        $st = $this->db->prepare($sql);
        $st->bindValue(':lim', $limit, PDO::PARAM_INT);
        $st->execute();

        $out = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $dias = (new DateTime($r['fecha_vencimiento']))->diff(new DateTime())->days;

            $out[] = [
                'name'      => $r['nombre'],
                'meta'      => "$dias día(s) vencido",
                'pill'      => '$'.number_format($r['saldo'], 2),
                'pillClass' => ($dias >= 7) ? 'danger' : 'warn'
            ];
        }
        return $out;
    }

    // 🔴 Corte pendiente del día anterior
    public function getAlertCortePendiente(): array {
        $sql = "
            SELECT COUNT(*)
            FROM cortes_caja
            WHERE fecha = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
        ";

        $existe = (int)$this->db->query($sql)->fetchColumn();

        return ($existe === 0)
            ? ['pendiente' => true, 'fecha' => date('Y-m-d', strtotime('-1 day'))]
            : ['pendiente' => false];
    }

    /* ========================================================= */

    private function diaES($n): string {
        return ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'][$n-1];
    }

    private function mesES($n): string {
        return ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'][$n-1];
    }
}