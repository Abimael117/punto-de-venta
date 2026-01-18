<?php

require_once __DIR__ . '/../../models/configuracion/ImpuestosModel.php';

class ImpuestosController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new ImpuestosModel();
    }

    public function index() {

        $impuestos = $this->model->getAll();

        return $this->view('configuracion/impuestos', [
            'title' => 'Impuestos',
            'css'   => ['configuracion/impuestos.css'],
            'js'    => ['configuracion/impuestos.js'], // opcional (si no lo tienes, quítalo)
            'impuestos' => $impuestos
        ]);
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $data = [
            'nombre'        => trim($_POST['nombre'] ?? ''),
            'tasa'          => floatval($_POST['tasa'] ?? 0),
            'tras_ret'      => $_POST['tras_ret'] ?? 'TRAS', // TRAS | RET
            'aplica_iva'    => isset($_POST['aplica_iva']) ? 1 : 0,
            'impreso'       => isset($_POST['impreso']) ? 1 : 0,
            'activo'        => isset($_POST['activo']) ? 1 : 0,
            'tipo'          => $_POST['tipo'] ?? 'TASA', // TASA | CUOTA | EXENTO
            'cuenta_contable'=> trim($_POST['cuenta_contable'] ?? ''),
            'orden'         => intval($_POST['orden'] ?? 0),
        ];

        if ($data['nombre'] === '') {
            header('Location: /configuracion/impuestos');
            exit;
        }

        $this->model->crear($data);

        header('Location: /configuracion/impuestos');
        exit;
    }

    public function toggle() {

        if (!isset($_GET['id'])) return;

        $id = intval($_GET['id']);
        $this->model->toggleActivo($id);

        header('Location: /configuracion/impuestos');
        exit;
    }
}
