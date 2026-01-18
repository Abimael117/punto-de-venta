<?php

require_once __DIR__ . '/../../models/operaciones/AjusteInventarioModel.php';

class AjusteInventarioController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new AjusteInventarioModel();
    }

    // GET /operaciones/ajuste-inventario
    public function index() {
        return $this->view('operaciones/ajuste_inventario', [
            'title' => 'Ajuste de Inventario',
            'css'   => ['operaciones/ajuste_inventario.css'],
            'js'    => ['operaciones/ajuste_inventario.js']
        ]);
    }

    // GET /operaciones/ajuste-inventario/articulos?q=
    public function articulos() {
        header('Content-Type: application/json; charset=utf-8');

        $q = trim($_GET['q'] ?? '');

        try {
            $data = $this->model->listarArticulos($q);
            echo json_encode(['ok' => true, 'data' => $data]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'ok'  => false,
                'msg' => $e->getMessage() ?: 'Error al cargar artículos'
            ]);
        }
        exit;
    }

    // POST /operaciones/ajuste-inventario/guardar
    public function guardar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $cab = $data['cabecera'] ?? [];
        $det = $data['detalle'] ?? [];

        $usuario_id = $_SESSION['user']['id'] ?? null;

        try {
            $res = $this->model->crear($cab, $det, $usuario_id);
            echo json_encode(['ok' => true, 'id' => $res['id']]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => $e->getMessage() ?: 'Error al guardar ajuste']);
        }
        exit;
    }
}
