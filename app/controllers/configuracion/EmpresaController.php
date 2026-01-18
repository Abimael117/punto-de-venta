<?php

require_once __DIR__ . '/../../models/configuracion/EmpresaModel.php';

class EmpresaController extends Controller {

    private $empresaModel;

    public function __construct() {
        AuthMiddleware::check();
        $this->empresaModel = new EmpresaModel();
    }

    /**
     * Vista principal de Empresa
     */
    public function index() {

        $empresa = $this->empresaModel->getEmpresa();

        return $this->view('configuracion/empresa', [
            'title'   => 'Empresa',
            'empresa' => $empresa,
            'css'     => ['configuracion/empresa.css']
        ]);
    }

    /**
     * Guardar datos (insert o update automático)
     */
    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $data = [
                'nombre'    => trim($_POST['nombre']),
                'direccion' => trim($_POST['direccion']),
                'ciudad'    => trim($_POST['ciudad']),
                'estado'    => trim($_POST['estado']),
                'cp'        => trim($_POST['cp']),
                'telefono'  => trim($_POST['telefono']),
                'email'     => trim($_POST['email']),
            ];

            if ($this->empresaModel->existeEmpresa()) {
                $this->empresaModel->updateEmpresa($data);
            } else {
                $this->empresaModel->insertEmpresa($data);
            }

            $_SESSION['flash'] = 'Datos de la empresa guardados correctamente';

            header('Location: /configuracion/empresa');
            exit;
        }
    }
}
