<?php

class InventarioMovimientosModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function entrada($articuloId, $origen, $referenciaId, $cantidad, $costo = 0) {

        $stmt = $this->db->prepare("
            INSERT INTO inventario_movimientos
            (articulo_id, tipo, origen, referencia_id, cantidad, costo)
            VALUES
            (:articulo, 'ENTRADA', :origen, :ref, :cantidad, :costo)
        ");

        $stmt->execute([
            ':articulo' => $articuloId,
            ':origen'   => $origen,
            ':ref'      => $referenciaId,
            ':cantidad' => $cantidad,
            ':costo'    => $costo
        ]);
    }

    public function salida($articuloId, $origen, $referenciaId, $cantidad) {

        $stmt = $this->db->prepare("
            INSERT INTO inventario_movimientos
            (articulo_id, tipo, origen, referencia_id, cantidad, costo)
            VALUES
            (:articulo, 'SALIDA', :origen, :ref, :cantidad, 0)
        ");

        $stmt->execute([
            ':articulo' => $articuloId,
            ':origen'   => $origen,
            ':ref'      => $referenciaId,
            ':cantidad' => $cantidad
        ]);
    }

    public function obtenerStock($articuloId) {

        $stmt = $this->db->prepare("
            SELECT
                SUM(CASE WHEN tipo = 'ENTRADA' THEN cantidad ELSE 0 END) -
                SUM(CASE WHEN tipo = 'SALIDA'  THEN cantidad ELSE 0 END) AS stock
            FROM inventario_movimientos
            WHERE articulo_id = :articulo
        ");

        $stmt->execute([
            ':articulo' => $articuloId
        ]);

        return (float) ($stmt->fetchColumn() ?? 0);
    }
}
