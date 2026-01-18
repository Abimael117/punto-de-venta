<?php

require_once __DIR__ . '/../../models/configuracion/ImpresorasModel.php';

class ImpresorasController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new ImpresorasModel();
    }

    public function index() {

        $impresoras = $this->model->getAll();

        return $this->view('configuracion/impresoras', [
            'title'      => 'Impresoras',
            'impresoras' => $impresoras,
            'css'        => ['configuracion/impresoras.css']
        ]);
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);

            if ($nombre !== '') {
                $this->model->create($nombre);
            }

            header('Location: /configuracion/impresoras');
            exit;
        }
    }

    public function toggle() {

        $id     = $_GET['id'];
        $estado = $_GET['estado'];

        $this->model->toggle($id, $estado);

        header('Location: /configuracion/impresoras');
        exit;
    }
}
