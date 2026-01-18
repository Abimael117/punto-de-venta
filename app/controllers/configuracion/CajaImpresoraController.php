<?php

require_once __DIR__ . '/../../models/configuracion/CajaImpresoraModel.php';

class CajaImpresoraController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new CajaImpresoraModel();
    }

    public function index() {
        return $this->view('configuracion/caja_impresora', [
            'title'      => 'Impresoras por Caja',
            'asignaciones' => $this->model->getAll(),
            'cajas'      => $this->model->getCajas(),
            'impresoras' => $this->model->getImpresoras(),
            'css'        => ['configuracion/caja_impresora.css']
        ]);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->model->insert(
                $_POST['caja_id'],
                $_POST['impresora_id']
            );

            header('Location: /configuracion/caja-impresora');
            exit;
        }
    }

    public function toggle() {
        $this->model->toggle($_GET['id'], $_GET['estado']);
        header('Location: /configuracion/caja-impresora');
        exit;
    }
}
