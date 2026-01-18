<?php

class UnidadesModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT *
            FROM unidades
            WHERE activo = 1
            ORDER BY (LOWER(nombre) = 'pza') DESC, nombre ASC
        ");
        return $stmt->fetchAll();
    }



    public function insert($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO unidades (nombre, abreviatura) 
             VALUES (:nombre, :abreviatura)"
        );

        return $stmt->execute([
            ':nombre'       => $data['nombre'],
            ':abreviatura'  => $data['abreviatura']
        ]);
    }

    public function toggle($id, $estado) {
        $stmt = $this->db->prepare(
            "UPDATE unidades SET activo = :estado WHERE id = :id"
        );

        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }
}
