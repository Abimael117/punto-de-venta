<?php

require_once __DIR__ . '/../../models/operaciones/CuentasCobrarModel.php';

class CuentasCobrarController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new CuentasCobrarModel();
    }

    // GET /operaciones/cuentas-por-cobrar
    public function index() {
        return $this->view('operaciones/cuentas_cobrar', [
            'title' => 'Cuentas por Cobrar',
            'css'   => ['operaciones/cuentas_cobrar.css'],
            'js'    => ['operaciones/cuentas_cobrar.js']
        ]);
    }

    // GET /operaciones/cuentas-por-cobrar/listar?estado=PENDIENTE|PAGADO|TODOS
    public function listar() {
        header('Content-Type: application/json; charset=utf-8');

        $estado = strtoupper(trim($_GET['estado'] ?? 'PENDIENTE'));

        try {
            $data = $this->model->listar($estado);
            echo json_encode(['ok' => true, 'data' => $data]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al listar cuentas por cobrar']);
        }
        exit;
    }

    // GET /operaciones/cuentas-por-cobrar/detalle?credito_id=123
    public function detalle() {
        header('Content-Type: application/json; charset=utf-8');

        $credito_id = intval($_GET['credito_id'] ?? 0);
        if ($credito_id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'credito_id inválido']);
            exit;
        }

        try {
            $credito = $this->model->getCredito($credito_id);
            if (!$credito) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'msg' => 'Crédito no encontrado']);
                exit;
            }

            $pagos = $this->model->listarPagos($credito_id);
            echo json_encode(['ok' => true, 'data' => ['credito' => $credito, 'pagos' => $pagos]]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al obtener detalle']);
        }
        exit;
    }

    // POST /operaciones/cuentas-por-cobrar/abonar
    public function abonar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $credito_id = intval($data['credito_id'] ?? 0);
        $monto = floatval($data['monto'] ?? 0);
        $metodo = strtoupper(trim($data['metodo'] ?? 'EFECTIVO'));
        $referencia = trim($data['referencia'] ?? '');
        $usuario_id = $_SESSION['user']['id'] ?? null;

        if ($credito_id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Crédito inválido']);
            exit;
        }
        if ($monto <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Monto inválido']);
            exit;
        }

        $valid = ['EFECTIVO','TARJETA','TRANSFERENCIA'];
        if (!in_array($metodo, $valid, true)) $metodo = 'EFECTIVO';

        try {
            $credito = $this->model->getCredito($credito_id);
            if (!$credito) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'msg' => 'Crédito no encontrado']);
                exit;
            }

            $saldo = floatval($credito['saldo'] ?? 0);
            $estado = strtoupper($credito['estado'] ?? 'PENDIENTE');

            if ($estado !== 'PENDIENTE' || $saldo <= 0) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'msg' => 'Este crédito ya está saldado']);
                exit;
            }

            if ($monto > $saldo) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'msg' => 'El pago no puede ser mayor al saldo']);
                exit;
            }

            $idPago = $this->model->registrarPago($credito_id, $usuario_id, $monto, $metodo, $referencia ?: null);

            echo json_encode(['ok' => true, 'id' => $idPago]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al registrar pago']);
        }

        exit;
    }
}
