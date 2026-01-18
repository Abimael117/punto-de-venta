<?php

class ImpuestosModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM impuestos ORDER BY orden ASC, id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActivos() {
        $stmt = $this->db->query("
            SELECT * 
            FROM impuestos 
            WHERE activo = 1
            ORDER BY orden ASC, id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function crear($data) {

        $stmt = $this->db->prepare("
            INSERT INTO impuestos
            (nombre, tasa, tras_ret, aplica_iva, impreso, activo, tipo, cuenta_contable, orden)
            VALUES
            (:nombre, :tasa, :tras_ret, :aplica_iva, :impreso, :activo, :tipo, :cuenta_contable, :orden)
        ");

        $stmt->execute([
            ':nombre'         => $data['nombre'],
            ':tasa'           => $data['tasa'],
            ':tras_ret'       => $data['tras_ret'],
            ':aplica_iva'     => $data['aplica_iva'],
            ':impreso'        => $data['impreso'],
            ':activo'         => $data['activo'],
            ':tipo'           => $data['tipo'],
            ':cuenta_contable'=> $data['cuenta_contable'],
            ':orden'          => $data['orden'],
        ]);
    }

    public function toggleActivo($id) {
        $stmt = $this->db->prepare("UPDATE impuestos SET activo = IF(activo=1,0,1) WHERE id=:id");
        $stmt->execute([':id' => $id]);
    }
}
