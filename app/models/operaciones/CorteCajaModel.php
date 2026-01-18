<?php

class CorteCajaModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    // =========================
    // TABLAS / CAMPOS VENTAS
    // =========================
    private string $T_VENTAS   = 'ventas';
    private string $C_FECHA    = 'created_at';
    private string $C_TOTAL    = 'total';
    private string $C_EFE      = 'efectivo';
    private string $C_TAR      = 'tarjeta';
    private string $C_TRA      = 'transferencia';
    private string $C_ES_CRED  = 'es_credito';

    // =========================
    // ✅ TABLAS / CAMPOS COMPRAS (SALIDA DE CAJA)
    // =========================
    private string $T_COMPRAS         = 'compras';
    private string $C_COMPRA_FECHA    = 'fecha_hora';
    private string $C_COMPRA_TOTAL    = 'total';
    private string $C_COMPRA_PAG_CAJA = 'pagada_con_caja';
    // (opcional si quieres validar solo efectivo)
    private string $C_COMPRA_METODO   = 'metodo_pago';

    // GET resumen por fecha
    public function getResumenPorFecha(string $fecha): array {

        // =========================
        // RESUMEN VENTAS
        // =========================
        $sql = "
            SELECT
              COALESCE(SUM({$this->C_TOTAL}), 0) AS total,
              COALESCE(SUM({$this->C_EFE}), 0)   AS efectivo,
              COALESCE(SUM({$this->C_TAR}), 0)   AS tarjeta,
              COALESCE(SUM({$this->C_TRA}), 0)   AS transferencia,
              COALESCE(SUM(
                CASE
                  WHEN {$this->C_ES_CRED} = 1
                    THEN GREATEST({$this->C_TOTAL} - ({$this->C_EFE}+{$this->C_TAR}+{$this->C_TRA}), 0)
                  ELSE 0
                END
              ), 0) AS credito
            FROM {$this->T_VENTAS}
            WHERE DATE({$this->C_FECHA}) = :fecha
        ";

        $st = $this->db->prepare($sql);
        $st->bindValue(':fecha', $fecha);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];

        // =========================
        // ✅ COMPRAS PAGADAS CON CAJA (SALIDA EFECTIVO)
        // =========================
        $sql2 = "
            SELECT
              COALESCE(SUM({$this->C_COMPRA_TOTAL}), 0) AS compras_caja
            FROM {$this->T_COMPRAS}
            WHERE DATE({$this->C_COMPRA_FECHA}) = :fecha
              AND {$this->C_COMPRA_PAG_CAJA} = 1
        ";

        // ✅ Si quieres forzar SOLO EFECTIVO, descomenta:
        // $sql2 .= " AND {$this->C_COMPRA_METODO} = 'EFECTIVO' ";

        $st2 = $this->db->prepare($sql2);
        $st2->bindValue(':fecha', $fecha);
        $st2->execute();
        $row2 = $st2->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'fecha' => $fecha,
            'total' => floatval($row['total'] ?? 0),
            'efectivo' => floatval($row['efectivo'] ?? 0),
            'tarjeta' => floatval($row['tarjeta'] ?? 0),
            'transferencia' => floatval($row['transferencia'] ?? 0),
            'credito' => floatval($row['credito'] ?? 0),

            // ✅ nuevo
            'compras_caja' => floatval($row2['compras_caja'] ?? 0),
        ];
    }

    // POST guardar corte
    public function guardarCorte(array $d): int {
        $sql = "
          INSERT INTO cortes_caja(
            fecha, hora_corte, usuario_id,
            efectivo_sistema, tarjeta_sistema, transferencia_sistema, credito_sistema, total_sistema,
            efectivo_contado, diferencia, notas
          )
          VALUES (
            :fecha, :hora_corte, :usuario_id,
            :efectivo_sistema, :tarjeta_sistema, :transferencia_sistema, :credito_sistema, :total_sistema,
            :efectivo_contado, :diferencia, :notas
          )
        ";

        $st = $this->db->prepare($sql);
        $st->bindValue(':fecha', $d['fecha']);
        $st->bindValue(':hora_corte', $d['hora']);
        $st->bindValue(':usuario_id', $d['usuario_id']);
        $st->bindValue(':efectivo_sistema', $d['efectivo_sistema']);
        $st->bindValue(':tarjeta_sistema', $d['tarjeta_sistema']);
        $st->bindValue(':transferencia_sistema', $d['transferencia_sistema']);
        $st->bindValue(':credito_sistema', $d['credito_sistema']);
        $st->bindValue(':total_sistema', $d['total_sistema']);
        $st->bindValue(':efectivo_contado', $d['efectivo_contado']);
        $st->bindValue(':diferencia', $d['diferencia']);
        $st->bindValue(':notas', $d['notas']);
        $st->execute();

        return intval($this->db->lastInsertId());
    }

    // GET listar historial
    public function listar(string $desde, string $hasta): array {
        $sql = "
          SELECT
            id, fecha, hora_corte,
            total_sistema, efectivo_sistema, tarjeta_sistema, transferencia_sistema, credito_sistema,
            efectivo_contado, diferencia, notas, creado_en
          FROM cortes_caja
          WHERE fecha BETWEEN :desde AND :hasta
          ORDER BY fecha DESC, hora_corte DESC, id DESC
        ";
        $st = $this->db->prepare($sql);
        $st->bindValue(':desde', $desde);
        $st->bindValue(':hasta', $hasta);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getById(int $id): ?array {
        $st = $this->db->prepare("SELECT * FROM cortes_caja WHERE id = :id LIMIT 1");
        $st->bindValue(':id', $id);
        $st->execute();
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
