<?php

require_once __DIR__ . '/../../models/configuracion/EmpleadosModel.php';

class EmpleadosController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new EmpleadosModel();
    }

    public function index() {

        $empleados = $this->model->getAll();

        return $this->view('configuracion/empleados', [
            'title'     => 'Empleados',
            'empleados' => $empleados,
            'css'       => ['configuracion/empleados.css']
        ]);
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'nombre'   => trim($_POST['nombre']),
                'telefono' => trim($_POST['telefono'])
            ];

            $this->model->create($data);

            header('Location: /configuracion/empleados');
            exit;
        }
    }

    public function toggle() {

        $id     = $_GET['id'];
        $estado = $_GET['estado'];

        $this->model->toggle($id, $estado);

        header('Location: /configuracion/empleados');
        exit;
    }
}
