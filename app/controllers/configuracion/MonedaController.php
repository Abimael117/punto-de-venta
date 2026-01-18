<?php

require_once __DIR__ . '/../../models/configuracion/MonedaModel.php';

class MonedaController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new MonedaModel();
    }

    public function index() {
        $monedas = $this->model->getAll();

        return $this->view('configuracion/moneda', [
            'title'   => 'Moneda',
            'monedas' => $monedas,
            'css'     => ['configuracion/moneda.css']
        ]);
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre  = trim($_POST['nombre']);
            $simbolo = trim($_POST['simbolo']);

            if ($nombre !== '' && $simbolo !== '') {
                $this->model->save($nombre, $simbolo);
                $_SESSION['flash'] = 'Moneda configurada correctamente';
            }

            header('Location: /configuracion/moneda');
            exit;
        }
    }

    public function activar() {
        $id = $_GET['id'];
        $this->model->toggle($id);

        header('Location: /configuracion/moneda');
        exit;
    }
}
