<?php

class ImpresorasModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM impresoras ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($nombre) {
        $stmt = $this->db->prepare(
            "INSERT INTO impresoras (nombre, activo) VALUES (:nombre, 1)"
        );
        return $stmt->execute([
            ':nombre' => $nombre
        ]);
    }

    public function toggle($id, $estado) {
        $stmt = $this->db->prepare(
            "UPDATE impresoras SET activo = :estado WHERE id = :id"
        );
        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }
}
