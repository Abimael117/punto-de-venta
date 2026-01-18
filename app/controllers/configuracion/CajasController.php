<?php

require_once __DIR__ . '/../../models/configuracion/CajasModel.php';

class CajasController extends Controller {

    private $cajasModel;

    public function __construct() {
        AuthMiddleware::check();
        $this->cajasModel = new CajasModel();
    }

    public function index() {

        $cajas = $this->cajasModel->getAll();

        return $this->view('configuracion/cajas', [
            'title' => 'Cajas',
            'cajas' => $cajas,
            'css'   => ['configuracion/cajas.css']
        ]);
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);

            if ($nombre !== '') {
                $this->cajasModel->insert($nombre);
            }

            header('Location: /configuracion/cajas');
            exit;
        }
    }

    public function toggle() {

        if (isset($_GET['id'], $_GET['estado'])) {
            $this->cajasModel->toggle(
                (int)$_GET['id'],
                (int)$_GET['estado']
            );
        }

        header('Location: /configuracion/cajas');
        exit;
    }
}
