<?php

require_once __DIR__ . '/../../models/consultas/ComprasCreditoModel.php';
require_once __DIR__ . '/../../models/operaciones/ProveedoresModel.php';

class ComprasCreditoController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new ComprasCreditoModel();
    }

    // GET /consultas/compras-credito
    public function index() {

        $proveedores = (new ProveedoresModel())->getAll();

        return $this->view('consultas/compras_credito', [
            'title'       => 'Compras a Crédito',
            'css'         => ['consultas/compras_credito.css'],
            'js'          => ['consultas/compras_credito.js'],
            'proveedores' => $proveedores
        ]);
    }

    // GET /consultas/compras-credito/listar?proveedor_id=&estatus=&q=&desde=&hasta=
    public function listar() {
        header('Content-Type: application/json; charset=utf-8');

        $f = [
            'proveedor_id' => $_GET['proveedor_id'] ?? '',
            'estatus'      => $_GET['estatus'] ?? '',
            'q'            => trim($_GET['q'] ?? ''),
            'desde'        => $_GET['desde'] ?? '',
            'hasta'        => $_GET['hasta'] ?? '',
        ];

        try {
            $rows = $this->model->listar($f);
            $resumen = $this->model->resumen($f);
            echo json_encode(['ok' => true, 'rows' => $rows, 'resumen' => $resumen]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al listar cuentas por pagar']);
        }
        exit;
    }

    // GET /consultas/compras-credito/detalle?id=123
    public function detalle() {
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
            exit;
        }

        try {
            $data = $this->model->getDetalle($id);
            echo json_encode(['ok' => true, 'data' => $data]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al cargar detalle']);
        }
        exit;
    }

    // POST /consultas/compras-credito/abonar
    // Body JSON: { cxp_id|cuenta_pagar_id, monto, fecha, metodo, referencia, notas, afecta_caja }
    public function abonar() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        try {
            $res = $this->model->abonar($data);
            echo json_encode(['ok' => true, 'data' => $res]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => $e->getMessage() ?: 'Error al registrar abono']);
        }
        exit;
    }

    // POST /consultas/compras-credito/marcarPagada
    // Body JSON: { cxp_id | cuenta_pagar_id }
    public function marcarPagada() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];

        // compat: cxp_id o cuenta_pagar_id
        $id = (int)($data['cxp_id'] ?? ($data['cuenta_pagar_id'] ?? 0));

        try {
            $this->model->marcarPagada($id);
            echo json_encode(['ok' => true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'msg' => 'Error al marcar como pagada']);
        }
        exit;
    }
}
