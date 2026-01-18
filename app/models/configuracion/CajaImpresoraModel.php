<?php

class CajaImpresoraModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $sql = "
            SELECT ci.id, c.nombre AS caja, i.nombre AS impresora, ci.activo
            FROM caja_impresora ci
            JOIN cajas c ON ci.caja_id = c.id
            JOIN impresoras i ON ci.impresora_id = i.id
        ";
        return $this->db->query($sql)->fetchAll();
    }

    public function getCajas() {
        return $this->db->query(
            "SELECT id, nombre FROM cajas WHERE activo = 1"
        )->fetchAll();
    }

    public function getImpresoras() {
        return $this->db->query(
            "SELECT id, nombre FROM impresoras WHERE activo = 1"
        )->fetchAll();
    }

    public function insert($caja_id, $impresora_id) {
        $stmt = $this->db->prepare(
            "INSERT INTO caja_impresora (caja_id, impresora_id) VALUES (?, ?)"
        );
        return $stmt->execute([$caja_id, $impresora_id]);
    }

    public function toggle($id, $estado) {
        $stmt = $this->db->prepare(
            "UPDATE caja_impresora SET activo = ? WHERE id = ?"
        );
        return $stmt->execute([$estado, $id]);
    }
}
