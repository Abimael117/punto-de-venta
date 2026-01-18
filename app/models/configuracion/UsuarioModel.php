<?php

class UsuarioModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM usuarios ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO usuarios (nombre, usuario, password, rol, activo)
                VALUES (:nombre, :usuario, :password, :rol, 1)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre'   => $data['nombre'],
            ':usuario'  => $data['usuario'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
            ':rol'      => $data['rol']
        ]);
    }

    public function toggle($id, $estado) {
        $stmt = $this->db->prepare(
            "UPDATE usuarios SET activo = :estado WHERE id = :id"
        );
        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }
}
