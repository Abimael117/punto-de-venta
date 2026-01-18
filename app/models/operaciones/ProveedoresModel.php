<?php

class ProveedoresModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->prepare(
            "SELECT * FROM proveedores ORDER BY id DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($nombre, $telefono, $email) {
        $stmt = $this->db->prepare(
            "INSERT INTO proveedores (nombre, telefono, email, activo)
             VALUES (:nombre, :telefono, :email, 1)"
        );

        return $stmt->execute([
            ':nombre'   => $nombre,
            ':telefono' => $telefono,
            ':email'    => $email
        ]);
    }

    public function getAllActivos() {
        $stmt = $this->db->prepare("SELECT id, nombre FROM proveedores WHERE activo = 1 ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function toggle($id, $estado) {
        $stmt = $this->db->prepare(
            "UPDATE proveedores SET activo = :estado WHERE id = :id"
        );

        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }
}
