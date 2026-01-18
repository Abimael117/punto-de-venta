<?php

class TagsModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM tags ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function guardar($nombre)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO tags (nombre, activo) VALUES (:nombre, 1)"
        );
        $stmt->execute(['nombre' => $nombre]);
    }

    public function toggle($id, $estado)
    {
        $stmt = $this->db->prepare(
            "UPDATE tags SET activo = :estado WHERE id = :id"
        );
        $stmt->execute([
            'estado' => $estado,
            'id' => $id
        ]);
    }
}
