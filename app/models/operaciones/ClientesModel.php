<?php

class ClientesModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /* =========================
       CONSULTAS BÁSICAS
    ========================= */

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

    public function getActivos() {
        return $this->getAll();
    }

    public function getById(int $id) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM clientes
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* =========================
       PÚBLICO EN GENERAL (POS)
       👉 ESTE MÉTODO ES CLAVE
    ========================= */

    public function getPublicoGeneral() {
        $stmt = $this->db->prepare("
            SELECT *
            FROM clientes
            WHERE
                codigo = 'CLI0003'
                OR nombre = 'Publico en General'
                OR nombre = 'Público en General'
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* =========================
       CREACIÓN / EDICIÓN
    ========================= */

    private function generarCodigo(): string {
        $stmt = $this->db->query("SELECT MAX(id) FROM clientes");
        $next = ((int)$stmt->fetchColumn()) + 1;
        return 'CLI' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data): bool {

        $stmt = $this->db->prepare("
            INSERT INTO clientes (
                codigo, nombre, telefono, email, direccion,
                permite_credito, limite_credito, dias_credito, activo
            ) VALUES (
                :codigo, :nombre, :telefono, :email, :direccion,
                :permite_credito, :limite_credito, :dias_credito, 1
            )
        ");

        return $stmt->execute([
            ':codigo'          => $this->generarCodigo(),
            ':nombre'          => $data['nombre'],
            ':telefono'        => $data['telefono'] ?? null,
            ':email'           => $data['email'] ?? null,
            ':direccion'       => $data['direccion'] ?? null,
            ':permite_credito' => (int)($data['permite_credito'] ?? 0),
            ':limite_credito'  => (float)($data['limite_credito'] ?? 0),
            ':dias_credito'    => (int)($data['dias_credito'] ?? 0),
        ]);
    }

    public function update(int $id, array $data): bool {

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
            ':telefono'        => $data['telefono'] ?? null,
            ':email'           => $data['email'] ?? null,
            ':direccion'       => $data['direccion'] ?? null,
            ':permite_credito' => (int)($data['permite_credito'] ?? 0),
            ':limite_credito'  => (float)($data['limite_credito'] ?? 0),
            ':dias_credito'    => (int)($data['dias_credito'] ?? 0),
            ':id'              => $id
        ]);
    }

    public function toggle(int $id, int $estado): bool {
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

    /* =========================
       BLOQUEOS / VALIDACIONES
    ========================= */

    public function getSaldoPendienteCliente(int $clienteId): float {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(c.saldo), 0)
            FROM creditos c
            WHERE c.cliente_id = :id
              AND c.estado <> 'CANCELADO'
        ");
        $stmt->execute([':id' => $clienteId]);
        return (float)$stmt->fetchColumn();
    }

    public function validarEliminacion(int $clienteId): void {

        $cli = $this->getById($clienteId);
        if (!$cli) {
            throw new Exception('Cliente no existe');
        }

        if (
            $cli['codigo'] === 'CLI0003'
            || $cli['nombre'] === 'Publico en General'
            || $cli['nombre'] === 'Público en General'
        ) {
            throw new Exception('No puedes eliminar al cliente Público en General.');
        }

        $saldo = $this->getSaldoPendienteCliente($clienteId);
        if ($saldo > 0) {
            throw new Exception(
                'No puedes eliminar: el cliente tiene crédito pendiente ($' .
                number_format($saldo, 2) . ').'
            );
        }
    }

    /* =========================
       POS: CREACIÓN RÁPIDA
    ========================= */

    public function createRapidoReturn(array $data): array {

        $nombre = trim($data['nombre'] ?? '');
        if ($nombre === '') {
            throw new Exception('El nombre es obligatorio');
        }

        $codigo = trim($data['codigo'] ?? '');
        if ($codigo === '') {
            $codigo = $this->generarCodigo();
        }

        $stmt = $this->db->prepare("
            INSERT INTO clientes (
                codigo, nombre, telefono, email, direccion,
                permite_credito, limite_credito, dias_credito, activo
            ) VALUES (
                :codigo, :nombre, :telefono, :email, :direccion,
                :permite_credito, :limite_credito, :dias_credito, 1
            )
        ");

        $stmt->execute([
            ':codigo'          => $codigo,
            ':nombre'          => $nombre,
            ':telefono'        => $data['telefono'] ?? null,
            ':email'           => $data['email'] ?? null,
            ':direccion'       => $data['direccion'] ?? null,
            ':permite_credito' => (int)($data['permite_credito'] ?? 0),
            ':limite_credito'  => (float)($data['limite_credito'] ?? 0),
            ':dias_credito'    => (int)($data['dias_credito'] ?? 0),
        ]);

        return [
            'id'     => (int)$this->db->lastInsertId(),
            'codigo' => $codigo,
            'nombre' => $nombre
        ];
    }
}
