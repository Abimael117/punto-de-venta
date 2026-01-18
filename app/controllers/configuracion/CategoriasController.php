<?php

require_once __DIR__ . '/../../models/configuracion/CategoriasModel.php';

class CategoriasController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new CategoriasModel();
    }

    public function index() {

        $categorias = $this->model->getAll();

        return $this->view('configuracion/categorias', [
            'title'      => 'Categorías',
            'categorias' => $categorias,
            'css'        => ['configuracion/categorias.css']
        ]);
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre = trim($_POST['nombre']);

            if (!empty($_POST['id'])) {
                $this->model->update($_POST['id'], $nombre);
            } else {
                $this->model->insert($nombre);
            }

            header('Location: /configuracion/categorias');
            exit;
        }
    }

    public function toggle() {

        if (isset($_GET['id'], $_GET['estado'])) {
            $this->model->toggle($_GET['id'], $_GET['estado']);
        }

        header('Location: /configuracion/categorias');
        exit;
    }
}
