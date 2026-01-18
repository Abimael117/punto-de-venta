<?php

class RolesModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM roles ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save($nombre) {
        $stmt = $this->db->prepare(
            "INSERT INTO roles (nombre) VALUES (:nombre)"
        );
        return $stmt->execute([':nombre' => $nombre]);
    }

    public function toggle($id, $estado) {
        $stmt = $this->db->prepare(
            "UPDATE roles SET activo = :estado WHERE id = :id"
        );
        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }
}
