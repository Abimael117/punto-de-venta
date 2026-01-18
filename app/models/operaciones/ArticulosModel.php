<?php

class ArticulosModel {

    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    public function getAll(): array {

        $sql = "
            SELECT 
                a.*,
                c.nombre AS categoria,
                u.abreviatura AS unidad,
                (
                    SELECT COALESCE(SUM(
                        CASE
                            WHEN im.tipo = 'ENTRADA' THEN im.cantidad
                            WHEN im.tipo = 'SALIDA'  THEN -im.cantidad
                            ELSE 0
                        END
                    ), 0)
                    FROM inventario_movimientos im
                    WHERE im.articulo_id = a.id
                ) AS stock
            FROM articulos a
            JOIN categorias c ON c.id = a.categoria_id
            JOIN unidades u   ON u.id = a.unidad_id
            WHERE a.activo = 1
            ORDER BY a.id DESC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findByCodigo(string $codigo): ?array {

        $stmt = $this->db->prepare("
            SELECT *
            FROM articulos
            WHERE codigo = :codigo
            LIMIT 1
        ");

        $stmt->execute([':codigo' => $codigo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function buscarPorCodigo(string $codigo): ?array {

        $stmt = $this->db->prepare("
            SELECT
                a.id,
                a.codigo,
                a.nombre AS descripcion,
                a.precio_compra,
                a.precio_venta,
                a.categoria_id,
                a.unidad_id,
                (
                    SELECT COALESCE(SUM(
                        CASE
                            WHEN im.tipo = 'ENTRADA' THEN im.cantidad
                            WHEN im.tipo = 'SALIDA'  THEN -im.cantidad
                            ELSE 0
                        END
                    ), 0)
                    FROM inventario_movimientos im
                    WHERE im.articulo_id = a.id
                ) AS stock
            FROM articulos a
            WHERE a.activo = 1
              AND a.codigo = :codigo
            LIMIT 1
        ");

        $stmt->execute([':codigo' => $codigo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function create(
        $codigo,
        $nombre,
        $categoria,
        $unidad,
        $precioCompra,
        $precioVenta
    ): bool {

        $stmt = $this->db->prepare("
            INSERT INTO articulos
            (codigo, nombre, categoria_id, unidad_id, precio_compra, precio_venta, activo)
            VALUES
            (:codigo, :nombre, :categoria, :unidad, :precio_compra, :precio_venta, 1)
        ");

        return $stmt->execute([
            ':codigo'        => $codigo,
            ':nombre'        => $nombre,
            ':categoria'     => $categoria,
            ':unidad'        => $unidad,
            ':precio_compra' => $precioCompra,
            ':precio_venta'  => $precioVenta
        ]);
    }

    public function update(int $id, array $data): bool {

        $stmt = $this->db->prepare("
            UPDATE articulos SET
                codigo        = :codigo,
                nombre        = :nombre,
                categoria_id  = :categoria,
                unidad_id     = :unidad,
                precio_compra = :precio_compra,
                precio_venta  = :precio_venta
            WHERE id = :id
            LIMIT 1
        ");

        return $stmt->execute([
            ':codigo'        => $data['codigo'],
            ':nombre'        => $data['nombre'],
            ':categoria'     => $data['categoria_id'],
            ':unidad'        => $data['unidad_id'],
            ':precio_compra' => $data['precio_compra'],
            ':precio_venta'  => $data['precio_venta'],
            ':id'            => $id
        ]);
    }

    public function reactivar($id, $data): bool {

        $stmt = $this->db->prepare("
            UPDATE articulos SET
                nombre        = :nombre,
                categoria_id  = :categoria,
                unidad_id     = :unidad,
                precio_compra = :precio_compra,
                precio_venta  = :precio_venta,
                activo        = 1
            WHERE id = :id
        ");

        return $stmt->execute([
            ':nombre'        => $data['nombre'],
            ':categoria'     => $data['categoria_id'],
            ':unidad'        => $data['unidad_id'],
            ':precio_compra' => $data['precio_compra'],
            ':precio_venta'  => $data['precio_venta'],
            ':id'            => $id
        ]);
    }

    public function toggle($id, $estado): bool {

        $stmt = $this->db->prepare("
            UPDATE articulos SET activo = :estado WHERE id = :id
        ");

        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id
        ]);
    }
}
