<?php

class EmpresaModel {

    private $db;

    public function __construct() {
        // Conexión usando tu Database actual (Singleton)
        $this->db = Database::connect();
    }

    /**
     * Obtener la empresa (registro único)
     */
    public function getEmpresa() {
        $stmt = $this->db->prepare(
            "SELECT * FROM empresa ORDER BY id ASC LIMIT 1"
        );
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si ya existe un registro de empresa
     */
    public function existeEmpresa() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM empresa");
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Inserta la empresa (solo una vez)
     */
    public function insertEmpresa($data) {
        $sql = "INSERT INTO empresa (
                    nombre, direccion, ciudad, estado, cp, telefono, email
                ) VALUES (
                    :nombre, :direccion, :ciudad, :estado, :cp, :telefono, :email
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre'    => $data['nombre'],
            ':direccion' => $data['direccion'],
            ':ciudad'    => $data['ciudad'],
            ':estado'    => $data['estado'],
            ':cp'        => $data['cp'],
            ':telefono'  => $data['telefono'],
            ':email'     => $data['email'],
        ]);
    }

    /**
     * Actualiza la empresa existente
     */
    public function updateEmpresa($data) {
        $sql = "UPDATE empresa SET
                    nombre = :nombre,
                    direccion = :direccion,
                    ciudad = :ciudad,
                    estado = :estado,
                    cp = :cp,
                    telefono = :telefono,
                    email = :email
                WHERE id = (
                    SELECT id FROM empresa ORDER BY id ASC LIMIT 1
                )";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':nombre'    => $data['nombre'],
            ':direccion' => $data['direccion'],
            ':ciudad'    => $data['ciudad'],
            ':estado'    => $data['estado'],
            ':cp'        => $data['cp'],
            ':telefono'  => $data['telefono'],
            ':email'     => $data['email'],
        ]);
    }
}
