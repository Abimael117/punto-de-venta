<?php

require_once __DIR__ . '/../../models/operaciones/VentasModel.php';
require_once __DIR__ . '/../../models/operaciones/ClientesModel.php';

class VentasController extends Controller {

    private $model;
    private $clientes;

    public function __construct() {
        AuthMiddleware::check();
        $this->model    = new VentasModel();
        $this->clientes = new ClientesModel();
    }

    public function index() {

        // Cliente default: Público en General (si existe)
        $clienteDefault = $this->clientes->getPublicoGeneral();
        $clientes       = $this->clientes->getActivos();

        return $this->view('operaciones/ventas', [
            'title' => 'Ventas',
            'css'   => [
                'operaciones/ventas.css',
                'operaciones/modal_cobro.css',
                'operaciones/modal_clientes.css'
            ],
            'js'    => [
                'operaciones/ventas.js'
            ],
            'clientes'       => $clientes,
            'clienteDefault' => $clienteDefault
        ]);
    }

    /**
     * =========================
     * AJAX: GUARDAR VENTA
     * =========================
     */
    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (
            !isset($data['venta']) ||
            !isset($data['detalle']) ||
            !is_array($data['detalle']) ||
            empty($data['detalle'])
        ) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
            exit;
        }

        try {

            $this->model->crearVenta($data['venta'], $data['detalle']);

            echo json_encode(['ok' => true]);
            exit;

        } catch (Exception $e) {

            http_response_code(400);
            echo json_encode([
                'ok'  => false,
                'msg' => $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * =========================
     * AJAX: info crédito (adeudo actual + vencidos)
     * =========================
     * GET /operaciones/ventas/creditoInfo?cliente_id=#
     */
    public function creditoInfo() {

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        $clienteId = (int)($_GET['cliente_id'] ?? 0);
        if ($clienteId <= 0) {
            echo json_encode([
                'adeudo' => 0,
                'tieneVencidos' => false,
                'proximoVenc' => null,
                'diasRestantes' => null
            ]);
            exit;
        }

        try {

            $resumen = $this->model->getCreditoResumen($clienteId);

            echo json_encode([
                'adeudo'        => (float)$resumen['adeudo'],
                'tieneVencidos' => (bool)$resumen['tieneVencidos'],
                'proximoVenc'   => $resumen['proximoVenc'],
                'diasRestantes' => $resumen['diasRestantes']
            ]);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'adeudo' => 0,
                'tieneVencidos' => false,
                'proximoVenc' => null,
                'diasRestantes' => null
            ]);
            exit;
        }
    }


    /**
     * (Opcional para futuro) Clientes activos JSON
     */
    public function clientes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            exit;
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->clientes->getActivos());
        exit;
    }
}
