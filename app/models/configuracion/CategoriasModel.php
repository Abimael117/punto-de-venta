<?php

class CategoriasModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->query(
            "SELECT * FROM categorias ORDER BY orden ASC, nombre ASC"
        );
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT * FROM categorias WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function insert($nombre) {
        $stmt = $this->db->prepare(
            "INSERT INTO categorias (nombre) VALUES (:nombre)"
        );
        return $stmt->execute([':nombre' => $nombre]);
    }

    public function update($id, $nombre) {
        $stmt = $this->db->prepare(
            "UPDATE categorias SET nombre = :nombre WHERE id = :id"
        );
        return $stmt->execute([
            ':nombre' => $nombre,
            ':id'     => $id
        ]);
    }

    public function toggle($id, $estado) {
        $stmt = $this->db->prepare(
            "UPDATE categorias SET activo = :estado WHERE id = :id"
        );
        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }
}
