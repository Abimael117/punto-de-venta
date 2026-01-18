<?php

require_once __DIR__ . '/../../models/configuracion/RolesModel.php';

class RolesController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new RolesModel();
    }

    public function index() {
        $roles = $this->model->getAll();

        return $this->view('configuracion/roles', [
            'title' => 'Roles',
            'roles' => $roles,
            'css'   => ['configuracion/roles.css']
        ]);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);

            if ($nombre !== '') {
                $this->model->save($nombre);
                $_SESSION['flash'] = 'Rol agregado correctamente';
            }

            header('Location: /configuracion/roles');
            exit;
        }
    }

    public function toggle() {
        $id     = $_GET['id'];
        $estado = $_GET['estado'];

        $this->model->toggle($id, $estado);

        header('Location: /configuracion/roles');
        exit;
    }
}
