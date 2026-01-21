<?php

require_once __DIR__ . '/../../models/operaciones/ClientesModel.php';

class ClientesController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new ClientesModel();
    }

    // GET /operaciones/clientes
    public function index() {
        return $this->view('operaciones/clientes', [
            'title' => 'Clientes',
            'css'   => ['operaciones/clientes.css'],
            'js'    => ['operaciones/clientes.js'],
        ]);
    }

    // GET /operaciones/clientes/listar
    public function listar() {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $data = $this->model->getAll();
            echo json_encode(['ok' => true, 'data' => $data]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al cargar clientes']);
        }
        exit;
    }

    // GET /operaciones/clientes/obtener?id=#
    public function obtener() {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
            exit;
        }

        try {
            $row = $this->model->getById($id);
            echo json_encode(['ok' => true, 'data' => $row]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al obtener cliente']);
        }
        exit;
    }

    // POST /operaciones/clientes/guardar
    public function guardar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = $_POST;

        try {
            $ok = $this->model->create($data);
            echo json_encode(['ok' => (bool)$ok]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    // POST /operaciones/clientes/actualizar
    public function actualizar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
            exit;
        }

        try {
            $ok = $this->model->update($id, $_POST);
            echo json_encode(['ok' => (bool)$ok]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    // POST /operaciones/clientes/eliminar
    public function eliminar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
            exit;
        }

        try {
            $this->model->validarEliminacion($id);
            $ok = $this->model->toggle($id, 0);
            echo json_encode(['ok' => (bool)$ok]);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
        }
        exit;
    }

    // POST /operaciones/clientes/crearRapido
    public function crearRapido() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        try {
            // soporta JSON y form-data
            $raw  = file_get_contents('php://input');
            $json = json_decode($raw, true);
            $data = is_array($json) ? $json : $_POST;

            $cliente = $this->model->createRapidoReturn($data);

            echo json_encode(['ok' => true, 'cliente' => $cliente]);
            exit;

        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
            exit;
        }
    }

}
