<?php

require_once __DIR__ . '/../../models/configuracion/TagsModel.php';

class TagsController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new TagsModel();
    }

    public function index() {
        $tags = $this->model->getAll();

        return $this->view('configuracion/tags', [
            'title' => 'Tags',
            'tags'  => $tags,
            'css'   => ['configuracion/tags.css']
        ]);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre']);

            if ($nombre !== '') {
                $this->model->save($nombre);
                $_SESSION['flash'] = 'Tag agregado correctamente';
            }

            header('Location: /configuracion/tags');
            exit;
        }
    }

    public function toggle() {
        $id     = $_GET['id'];
        $estado = $_GET['estado'];

        $this->model->toggle($id, $estado);

        header('Location: /configuracion/tags');
        exit;
    }
}
