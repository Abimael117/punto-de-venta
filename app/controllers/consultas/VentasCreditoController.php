<?php

require_once __DIR__ . '/../../models/consultas/VentasCreditoModel.php';

class VentasCreditoController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new VentasCreditoModel();
    }

    // GET /consultas/ventas-credito
    public function index() {
        return $this->view('consultas/ventas_credito', [
            'title' => 'Ventas a Crédito',
            'css'   => ['consultas/ventas_credito.css'],
            'js'    => ['consultas/ventas_credito.js']
        ]);
    }

    // GET /consultas/ventas-credito/listar?desde=YYYY-MM-DD&hasta=YYYY-MM-DD&estado=all|vencidos|porvencer|aldia&q=...
    // LISTA TOTALIZADO POR CLIENTE
    public function listar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $desde  = trim($_GET['desde'] ?? '');
        $hasta  = trim($_GET['hasta'] ?? '');
        $estado = trim($_GET['estado'] ?? 'all');
        $q      = trim($_GET['q'] ?? '');

        try {
            $rows = $this->model->listarClientes([
                'desde'  => $desde,
                'hasta'  => $hasta,
                'estado' => $estado,
                'q'      => $q
            ]);

            echo json_encode(['ok' => true, 'data' => $rows]);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al cargar créditos']);
            exit;
        }
    }

    // GET /consultas/ventas-credito/detalle?cliente_id=#
    // DEVUELVE DESGLOSE DE CRÉDITOS DE ESE CLIENTE
    public function detalle() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $clienteId = (int)($_GET['cliente_id'] ?? ($_GET['id'] ?? 0));
        if ($clienteId <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Cliente inválido']);
            exit;
        }

        $desde  = trim($_GET['desde'] ?? '');
        $hasta  = trim($_GET['hasta'] ?? '');
        $estado = trim($_GET['estado'] ?? 'all');
        $q      = trim($_GET['q'] ?? '');

        try {
            $data = $this->model->getDetalleCliente($clienteId, [
                'desde'  => $desde,
                'hasta'  => $hasta,
                'estado' => $estado,
                'q'      => $q
            ]);

            if (!$data) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'msg' => 'Cliente no encontrado / sin créditos']);
                exit;
            }

            echo json_encode(['ok' => true, 'data' => $data]);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al obtener detalle']);
            exit;
        }
    }

    // GET /consultas/ventas-credito/credito?id=#
    // DETALLE DE UN CRÉDITO + PAGOS
    public function credito() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $creditoId = (int)($_GET['id'] ?? 0);
        if ($creditoId <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
            exit;
        }

        try {
            $data = $this->model->getDetalleCredito($creditoId);

            if (!$data) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'msg' => 'Crédito no encontrado']);
                exit;
            }

            echo json_encode(['ok' => true, 'data' => $data]);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al obtener crédito']);
            exit;
        }
    }
}
