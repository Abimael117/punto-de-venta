<?php

require_once __DIR__ . '/../../models/consultas/VentasDetalleModel.php';

class VentasDetalleController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new VentasDetalleModel();
    }

    // GET /consultas/ventas-detalle
    public function index() {
        return $this->view('consultas/ventas_detalle', [
            'title' => 'Detalle de Ventas',
            'css'   => ['consultas/ventas_detalle.css'],
            'js'    => ['consultas/ventas_detalle.js'],
        ]);
    }

    // GET /consultas/ventas-detalle/listar?desde=YYYY-MM-DD&hasta=YYYY-MM-DD&credito=all|1|0&q=
    public function listar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $desde   = trim((string)($_GET['desde'] ?? ''));
        $hasta   = trim((string)($_GET['hasta'] ?? ''));
        $credito = trim((string)($_GET['credito'] ?? 'all'));
        $q       = trim((string)($_GET['q'] ?? ''));

        try {
            $data = $this->model->listar([
                'desde'   => $desde,
                'hasta'   => $hasta,
                'credito' => $credito,
                'q'       => $q
            ]);

            echo json_encode(['ok' => true, 'data' => $data]);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al cargar detalle de ventas']);
            exit;
        }
    }

    // GET /consultas/ventas-detalle/detalle?venta_id=#
    public function detalle() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $ventaId = (int)($_GET['venta_id'] ?? 0);
        if ($ventaId <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'venta_id inválido']);
            exit;
        }

        try {
            $data = $this->model->getDetalleVenta($ventaId);
            echo json_encode(['ok' => true, 'data' => $data]);
            exit;

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al obtener detalle']);
            exit;
        }
    }
}
