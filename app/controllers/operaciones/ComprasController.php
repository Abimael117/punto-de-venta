<?php

require_once __DIR__ . '/../../models/operaciones/ComprasModel.php';
require_once __DIR__ . '/../../models/operaciones/ProveedoresModel.php';
require_once __DIR__ . '/../../models/configuracion/CategoriasModel.php';
require_once __DIR__ . '/../../models/configuracion/UnidadesModel.php';
require_once __DIR__ . '/../../models/configuracion/ImpuestosModel.php';

class ComprasController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new ComprasModel();
    }

    public function index() {

        $categorias  = (new CategoriasModel())->getAll();
        $unidades    = (new UnidadesModel())->getAll();
        $proveedores = (new ProveedoresModel())->getAll();

        // ✅ Impuestos activos
        $impuestos   = (new ImpuestosModel())->getActivos();

        return $this->view('operaciones/compras', [
            'title'       => 'Compras',
            'css'         => ['operaciones/compras.css'],
            'js'          => ['operaciones/compras.js'],
            'categorias'  => $categorias,
            'unidades'    => $unidades,
            'proveedores' => $proveedores,
            'impuestos'   => $impuestos
        ]);
    }

    // POST /operaciones/compras/guardar
    public function guardar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        try {
            $res = $this->model->crearCompra(
                $data['compra'] ?? [],
                $data['detalle'] ?? []
            );

            echo json_encode([
                'ok' => true,
                'compra_id' => $res['id'] ?? null,
                'folio' => $res['folio'] ?? null
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al guardar compra']);
        }

        exit;
    }

    // =========================
    // 🆕 GUARDAR PROVEEDOR (AJAX)
    // POST /operaciones/compras/guardarProveedor
    // =========================
    public function guardarProveedor() {

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $nombre   = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email    = trim($_POST['email'] ?? '');

        if ($nombre === '') {
            echo json_encode(['ok' => false, 'msg' => 'El nombre es obligatorio']);
            exit;
        }

        try {
            $db = Database::connect();

            $stmt = $db->prepare("
                INSERT INTO proveedores (nombre, telefono, email, activo)
                VALUES (:nombre, :telefono, :email, 1)
            ");

            $stmt->execute([
                ':nombre'   => $nombre,
                ':telefono' => $telefono,
                ':email'    => $email
            ]);

            $id = $db->lastInsertId();

            echo json_encode([
                'ok' => true,
                'proveedor' => [
                    'id'     => $id,
                    'nombre' => $nombre
                ]
            ]);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al guardar proveedor']);
            exit;
        }
    }
}
