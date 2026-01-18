<?php

class MonedaModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM moneda");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save($nombre, $simbolo) {
        // Desactiva todas antes (solo una activa)
        $this->db->exec("UPDATE moneda SET activo = 0");

        $stmt = $this->db->prepare(
            "INSERT INTO moneda (nombre, simbolo, activo) VALUES (?, ?, 1)"
        );
        return $stmt->execute([$nombre, $simbolo]);
    }

    public function toggle($id) {
        // Desactiva todas
        $this->db->exec("UPDATE moneda SET activo = 0");

        // Activa solo la seleccionada
        $stmt = $this->db->prepare(
            "UPDATE moneda SET activo = 1 WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }
}
