<?php

class ClientesModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Clientes activos
     */
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT *
            FROM clientes
            WHERE activo = 1
            ORDER BY id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Alias más claro (para otras pantallas)
     */
    public function getActivos() {
        return $this->getAll();
    }

    /**
     * Obtener cliente por id
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM clientes
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cliente "Público en General"
     * (lo busca por nombre o por código típico CLI0003)
     */
    public function getPublicoGeneral() {

        $stmt = $this->db->prepare("
            SELECT *
            FROM clientes
            WHERE
                (nombre = 'Publico en General'
                OR nombre = 'Público en General'
                OR codigo = 'CLI0003')
            LIMIT 1
        ");
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Código automático CLI0001
     */
    private function generarCodigo() {

        $stmt = $this->db->query("
            SELECT MAX(id) AS ultimo_id
            FROM clientes
        ");

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $next = ((int)($row['ultimo_id'] ?? 0)) + 1;

        return 'CLI' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Crear cliente (pantalla normal)
     */
    public function create($data) {

        $codigo = $this->generarCodigo();

        $stmt = $this->db->prepare("
            INSERT INTO clientes (
                codigo,
                nombre,
                telefono,
                email,
                direccion,
                permite_credito,
                limite_credito,
                dias_credito,
                activo
            ) VALUES (
                :codigo,
                :nombre,
                :telefono,
                :email,
                :direccion,
                :permite_credito,
                :limite_credito,
                :dias_credito,
                1
            )
        ");

        return $stmt->execute([
            ':codigo'          => $codigo,
            ':nombre'          => $data['nombre'],
            ':telefono'        => $data['telefono'],
            ':email'           => $data['email'],
            ':direccion'       => $data['direccion'],
            ':permite_credito' => $data['permite_credito'],
            ':limite_credito'  => $data['limite_credito'],
            ':dias_credito'    => $data['dias_credito']
        ]);
    }

    /**
     * Actualizar cliente
     */
    public function update($id, $data) {

        $stmt = $this->db->prepare("
            UPDATE clientes SET
                nombre = :nombre,
                telefono = :telefono,
                email = :email,
                direccion = :direccion,
                permite_credito = :permite_credito,
                limite_credito = :limite_credito,
                dias_credito = :dias_credito
            WHERE id = :id
        ");

        return $stmt->execute([
            ':nombre'          => $data['nombre'],
            ':telefono'        => $data['telefono'],
            ':email'           => $data['email'],
            ':direccion'       => $data['direccion'],
            ':permite_credito' => $data['permite_credito'],
            ':limite_credito'  => $data['limite_credito'],
            ':dias_credito'    => $data['dias_credito'],
            ':id'              => $id
        ]);
    }

    /**
     * Soft delete
     */
    public function toggle($id, $estado) {

        $stmt = $this->db->prepare("
            UPDATE clientes
            SET activo = :estado
            WHERE id = :id
        ");

        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }

    /**
     * =======================================================
     * POS: Crear cliente rápido y REGRESAR el cliente insertado
     * (para inyectarlo en el modal sin recargar / sin redirigir)
     * =======================================================
     */
    public function createRapidoReturn($data) {

        $nombre = trim((string)($data['nombre'] ?? ''));
        if ($nombre === '') {
            throw new Exception('El nombre es obligatorio');
        }

        $telefono  = isset($data['telefono']) ? trim((string)$data['telefono']) : null;
        $email     = isset($data['email']) ? trim((string)$data['email']) : null;
        $direccion = isset($data['direccion']) ? trim((string)$data['direccion']) : null;

        $permite = (int)($data['permite_credito'] ?? 0) === 1 ? 1 : 0;
        $limite  = (float)($data['limite_credito'] ?? 0);
        $dias    = (int)($data['dias_credito'] ?? 0);

        // por si viene un codigo (no lo usas ahora, pero lo soportamos)
        $codigoIn = isset($data['codigo']) ? trim((string)$data['codigo']) : '';

        // normaliza
        if ($telefono === '') $telefono = null;
        if ($email === '') $email = null;
        if ($direccion === '') $direccion = null;

        // código: si no mandan, genera
        $codigo = $codigoIn !== '' ? $codigoIn : $this->generarCodigo();

        // evita duplicado de código si mandaron uno manual
        if ($codigoIn !== '') {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM clientes WHERE codigo = :c LIMIT 1");
            $stmt->execute([':c' => $codigo]);
            if ((int)$stmt->fetchColumn() > 0) {
                throw new Exception('Ese código ya existe');
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO clientes (
                codigo,
                nombre,
                telefono,
                email,
                direccion,
                permite_credito,
                limite_credito,
                dias_credito,
                activo
            ) VALUES (
                :codigo,
                :nombre,
                :telefono,
                :email,
                :direccion,
                :permite_credito,
                :limite_credito,
                :dias_credito,
                1
            )
        ");

        $stmt->execute([
            ':codigo'          => $codigo,
            ':nombre'          => $nombre,
            ':telefono'        => $telefono,
            ':email'           => $email,
            ':direccion'       => $direccion,
            ':permite_credito' => $permite,
            ':limite_credito'  => $limite,
            ':dias_credito'    => $dias
        ]);

        $id = (int)$this->db->lastInsertId();

        // regresamos un payload perfecto para tu JS (res.cliente)
        return [
            'id'             => $id,
            'codigo'         => $codigo,
            'nombre'         => $nombre,
            'telefono'       => $telefono,
            'email'          => $email,
            'direccion'      => $direccion,
            'permite_credito'=> $permite,
            'limite_credito' => $limite,
            'dias_credito'   => $dias
        ];
    }
}
