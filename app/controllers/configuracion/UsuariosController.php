<?php

require_once __DIR__ . '/../../models/configuracion/UsuarioModel.php';

class UsuariosController extends Controller {

    private $usuarioModel;

    public function __construct() {
        AuthMiddleware::check();
        $this->usuarioModel = new UsuarioModel();
    }

    public function index() {

        $usuarios = $this->usuarioModel->getAll();

        return $this->view('configuracion/usuarios', [
            'title'    => 'Usuarios',
            'usuarios' => $usuarios,
            'css'      => ['configuracion/usuarios.css']
        ]);
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'nombre'   => trim($_POST['nombre']),
                'usuario'  => trim($_POST['usuario']),
                'password' => $_POST['password'],
                'rol'      => $_POST['rol']
            ];

            $this->usuarioModel->create($data);

            $_SESSION['flash'] = 'Usuario creado correctamente';

            header('Location: /configuracion/usuarios');
            exit;
        }
    }

    public function toggle() {

        $id     = $_GET['id'];
        $estado = $_GET['estado'];

        $this->usuarioModel->toggle($id, $estado);

        header('Location: /configuracion/usuarios');
        exit;
    }
}
