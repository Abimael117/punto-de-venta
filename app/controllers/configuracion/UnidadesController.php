<?php

require_once __DIR__ . '/../../models/configuracion/UnidadesModel.php';

class UnidadesController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new UnidadesModel();
    }

    public function index() {

        $unidades = $this->model->getAll();

        return $this->view('configuracion/unidades', [
            'title'    => 'Unidades',
            'unidades' => $unidades,
            'css'      => ['configuracion/unidades.css']
        ]);
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'nombre'      => trim($_POST['nombre']),
                'abreviatura' => trim($_POST['abreviatura'])
            ];

            $this->model->insert($data);

            header('Location: /configuracion/unidades');
            exit;
        }
    }

    public function toggle() {

        $id     = $_GET['id'];
        $estado = $_GET['estado'];

        $this->model->toggle($id, $estado);

        header('Location: /configuracion/unidades');
        exit;
    }
}
