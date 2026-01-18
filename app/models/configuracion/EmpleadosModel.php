<?php

class EmpleadosModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM empleados ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO empleados (nombre, telefono) VALUES (:nombre, :telefono)"
        );
        return $stmt->execute([
            ':nombre'   => $data['nombre'],
            ':telefono' => $data['telefono']
        ]);
    }

    public function toggle($id, $estado) {
        $stmt = $this->db->prepare(
            "UPDATE empleados SET activo = :estado WHERE id = :id"
        );
        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }
}
