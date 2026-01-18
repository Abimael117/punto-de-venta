<?php

require_once __DIR__ . '/../../models/operaciones/InventarioInicialModel.php';

class InventarioInicialController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new InventarioInicialModel();
    }

    // GET /operaciones/inventario-inicial
    public function index() {

        // ✅ Traer categorías y unidades para el modal "Agregar artículo"
        $db = Database::connect();

        // Categorías activas
        $stCat = $db->prepare("
            SELECT id, nombre
            FROM categorias
            WHERE activo = 1
            ORDER BY COALESCE(orden, 999999), nombre
        ");
        $stCat->execute();
        $categorias = $stCat->fetchAll(PDO::FETCH_ASSOC);

        // Unidades activas
        $stUni = $db->prepare("
            SELECT id, nombre, abreviatura
            FROM unidades
            WHERE activo = 1
            ORDER BY nombre
        ");
        $stUni->execute();
        $unidades = $stUni->fetchAll(PDO::FETCH_ASSOC);

        return $this->view('operaciones/inventario_inicial', [
            'title'      => 'Inventario Inicial',
            'css'        => ['operaciones/inventario_inicial.css'],
            'js'         => ['operaciones/inventario_inicial.js'],
            'categorias' => $categorias,  // ✅ importante
            'unidades'   => $unidades     // ✅ importante
        ]);
    }

    // POST /operaciones/inventario-inicial/guardar
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
            echo json_encode(['ok' => false, 'msg' => $e->getMessage() ?: 'Error al guardar inventario inicial']);
        }
        exit;
    }
}
