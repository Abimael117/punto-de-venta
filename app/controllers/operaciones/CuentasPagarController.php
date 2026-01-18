<?php

require_once __DIR__ . '/../../models/operaciones/CuentasPagarModel.php';

class CuentasPagarController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new CuentasPagarModel();
    }

    // GET /operaciones/cuentas-por-pagar
    public function index() {
        return $this->view('operaciones/cuentas_pagar', [
            'title' => 'Cuentas por Pagar',
            'css'   => ['operaciones/cuentas_pagar.css'],
            'js'    => ['operaciones/cuentas_pagar.js']
        ]);
    }

    // GET /operaciones/cuentas-por-pagar/listar?estado=PENDIENTE|PAGADA|TODOS
    public function listar() {
        header('Content-Type: application/json; charset=utf-8');

        $estado = strtoupper(trim($_GET['estado'] ?? 'PENDIENTE'));

        try {
            $data = $this->model->listar($estado);
            echo json_encode(['ok' => true, 'data' => $data]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al listar cuentas por pagar']);
        }
        exit;
    }

    // GET /operaciones/cuentas-por-pagar/detalle?cuenta_id=123
    public function detalle() {
        header('Content-Type: application/json; charset=utf-8');

        $cuenta_id = intval($_GET['cuenta_id'] ?? 0);
        if ($cuenta_id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'cuenta_id inválido']);
            exit;
        }

        try {
            $cuenta = $this->model->getCuenta($cuenta_id);
            if (!$cuenta) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'msg' => 'Cuenta por pagar no encontrada']);
                exit;
            }

            $pagos = $this->model->listarPagos($cuenta_id);
            echo json_encode(['ok' => true, 'data' => ['cuenta' => $cuenta, 'pagos' => $pagos]]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al obtener detalle']);
        }
        exit;
    }

    // POST /operaciones/cuentas-por-pagar/abonar
    public function abonar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        $cuenta_id  = intval($data['cuenta_id'] ?? 0);
        $monto      = floatval($data['monto'] ?? 0);
        $metodo     = strtoupper(trim($data['metodo'] ?? 'EFECTIVO'));
        $referencia = trim($data['referencia'] ?? '');
        $usuario_id = $_SESSION['user']['id'] ?? null;

        if ($cuenta_id <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Cuenta inválida']);
            exit;
        }
        if ($monto <= 0) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Monto inválido']);
            exit;
        }

        $valid = ['EFECTIVO','TARJETA','TRANSFERENCIA','CHEQUE','OTRO'];
        if (!in_array($metodo, $valid, true)) $metodo = 'EFECTIVO';

        try {
            $cuenta = $this->model->getCuenta($cuenta_id);
            if (!$cuenta) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'msg' => 'Cuenta no encontrada']);
                exit;
            }

            $saldo = floatval($cuenta['saldo'] ?? 0);
            $estado = strtoupper($cuenta['estatus'] ?? 'PENDIENTE');

            if ($estado !== 'PENDIENTE' || $saldo <= 0) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'msg' => 'Esta deuda ya está saldada']);
                exit;
            }

            if ($monto > $saldo) {
                http_response_code(400);
                echo json_encode(['ok' => false, 'msg' => 'El pago no puede ser mayor al saldo']);
                exit;
            }

            $idPago = $this->model->registrarPago($cuenta_id, $usuario_id, $monto, $metodo, $referencia ?: null);

            echo json_encode(['ok' => true, 'id' => $idPago]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al registrar pago']);
        }

        exit;
    }
}
