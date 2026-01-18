<?php

class CajasModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM cajas ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($nombre) {
        $stmt = $this->db->prepare(
            "INSERT INTO cajas (nombre) VALUES (:nombre)"
        );
        return $stmt->execute([
            ':nombre' => $nombre
        ]);
    }

    public function toggle($id, $estado) {
        $stmt = $this->db->prepare(
            "UPDATE cajas SET activo = :estado WHERE id = :id"
        );
        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }
}
