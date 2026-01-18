<?php

require_once __DIR__ . '/../../models/operaciones/CorteCajaModel.php';

class CorteCajaController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new CorteCajaModel();
    }

    public function index() {
        return $this->view('operaciones/corte_caja', [
            'title' => 'Corte de Caja',
            'css'   => ['operaciones/corte_caja.css'],
            'js'    => ['operaciones/corte_caja.js']
        ]);
    }

    // GET /operaciones/corte-caja/resumen?fecha=YYYY-MM-DD
    public function resumen() {
        header('Content-Type: application/json; charset=utf-8');

        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        try {
            $data = $this->model->getResumenPorFecha($fecha);
            echo json_encode(['ok' => true, 'data' => $data]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al obtener resumen']);
        }
        exit;
    }

    // POST /operaciones/corte-caja/guardar
    public function guardar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $fecha = $data['fecha'] ?? date('Y-m-d');
        $hora  = $data['hora'] ?? date('H:i');
        $efectivo_contado = floatval($data['efectivo_contado'] ?? 0);
        $notas = trim($data['notas'] ?? '');
        $usuario_id = $_SESSION['user']['id'] ?? null;

        try {
            $resumen = $this->model->getResumenPorFecha($fecha);

            // ✅ En BD dejamos diferencia contra efectivo_sistema (igual que antes)
            $efectivo_sistema = floatval($resumen['efectivo'] ?? 0);
            $diferencia = round($efectivo_contado - $efectivo_sistema, 2);

            $id = $this->model->guardarCorte([
                'fecha' => $fecha,
                'hora'  => $hora,
                'usuario_id' => $usuario_id,

                'efectivo_sistema' => $resumen['efectivo'] ?? 0,
                'tarjeta_sistema' => $resumen['tarjeta'] ?? 0,
                'transferencia_sistema' => $resumen['transferencia'] ?? 0,
                'credito_sistema' => $resumen['credito'] ?? 0,
                'total_sistema' => $resumen['total'] ?? 0,

                'efectivo_contado' => $efectivo_contado,
                'diferencia' => $diferencia,
                'notas' => $notas ?: null,
            ]);

            echo json_encode(['ok' => true, 'id' => $id]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al guardar el corte']);
        }

        exit;
    }

    // GET /operaciones/corte-caja/listar?desde=YYYY-MM-DD&hasta=YYYY-MM-DD
    public function listar() {
        header('Content-Type: application/json; charset=utf-8');

        $desde = $_GET['desde'] ?? date('Y-m-01');
        $hasta = $_GET['hasta'] ?? date('Y-m-d');

        try {
            $list = $this->model->listar($desde, $hasta);
            echo json_encode(['ok' => true, 'data' => $list]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al listar historial']);
        }
        exit;
    }

    // GET /operaciones/corte-caja/detalle?id=123
    public function detalle() {
        header('Content-Type: application/json; charset=utf-8');

        $id = intval($_GET['id'] ?? 0);

        try {
            $row = $this->model->getById($id);
            echo json_encode(['ok' => true, 'data' => $row]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al obtener detalle']);
        }
        exit;
    }
}
