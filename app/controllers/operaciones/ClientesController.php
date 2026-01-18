<?php

require_once __DIR__ . '/../../models/operaciones/ClientesModel.php';

class ClientesController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new ClientesModel();
    }

    public function index() {

        $clientes = $this->model->getAll();

        return $this->view('operaciones/clientes', [
            'title'    => 'Clientes',
            'clientes' => $clientes,
            'css'      => ['operaciones/clientes.css']
        ]);
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $data = [
            'nombre'          => $_POST['nombre'] ?? '',
            'telefono'        => $_POST['telefono'] ?? null,
            'email'           => $_POST['email'] ?? null,
            'direccion'       => $_POST['direccion'] ?? null,
            'permite_credito' => isset($_POST['permite_credito']) ? 1 : 0,
            'limite_credito'  => $_POST['limite_credito'] ?? 0,
            'dias_credito'    => $_POST['dias_credito'] ?? 0
        ];

        // 👉 editar
        if (!empty($_POST['id'])) {
            $this->model->update($_POST['id'], $data);
        }
        // 👉 agregar
        else {
            $this->model->create($data);
        }

        header('Location: /operaciones/clientes');
        exit;
    }

    public function eliminar() {

        if (!isset($_GET['id'])) return;

        $this->model->toggle($_GET['id'], 0);

        header('Location: /operaciones/clientes');
        exit;
    }

    /**
     * =========================
     * AJAX: Crear cliente rápido (POS)
     * POST JSON -> JSON
     * =========================
     * Ruta: /operaciones/clientes/crearRapido
     */
    public function crearRapido() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
            exit;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'JSON inválido']);
            exit;
        }

        $nombre = trim((string)($data['nombre'] ?? ''));
        if ($nombre === '') {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => 'El nombre es obligatorio']);
            exit;
        }

        $payload = [
            'nombre'          => $nombre,
            'telefono'        => isset($data['telefono']) ? trim((string)$data['telefono']) : null,
            'email'           => isset($data['email']) ? trim((string)$data['email']) : null,
            'direccion'       => isset($data['direccion']) ? trim((string)$data['direccion']) : null,
            'permite_credito' => (int)($data['permite_credito'] ?? 0) === 1 ? 1 : 0,
            'limite_credito'  => (float)($data['limite_credito'] ?? 0),
            'dias_credito'    => (int)($data['dias_credito'] ?? 0),
            // opcional si quisieras permitirlo, pero tu modal no lo manda:
            'codigo'          => isset($data['codigo']) ? trim((string)$data['codigo']) : null,
        ];

        try {

            // crea y regresa el cliente insertado (con id/codigo)
            $cliente = $this->model->createRapidoReturn($payload);

            echo json_encode([
                'ok' => true,
                'cliente' => $cliente
            ]);
            exit;

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
            exit;
        }
    }
}
