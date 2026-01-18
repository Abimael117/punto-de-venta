<?php

require_once __DIR__ . '/../../models/operaciones/ProveedoresModel.php';

class ProveedoresController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new ProveedoresModel();
    }

    public function index() {

        $proveedores = $this->model->getAll();

        return $this->view('operaciones/proveedores', [
            'title'       => 'Proveedores',
            'proveedores' => $proveedores,
            'css'         => ['operaciones/proveedores.css']
        ]);
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre   = trim($_POST['nombre']);
            $telefono = trim($_POST['telefono']);
            $email    = trim($_POST['email']);

            if ($nombre !== '') {
                $this->model->create($nombre, $telefono, $email);
            }

            header('Location: /operaciones/proveedores');
            exit;
        }
    }

    public function toggle() {

        $id     = $_GET['id'];
        $estado = $_GET['estado'];

        $this->model->toggle($id, $estado);

        header('Location: /operaciones/proveedores');
        exit;
    }
}
