<?php

require_once __DIR__ . '/../../models/operaciones/ArticulosModel.php';
require_once __DIR__ . '/../../models/configuracion/CategoriasModel.php';
require_once __DIR__ . '/../../models/configuracion/UnidadesModel.php';

class ArticulosController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check();
        $this->model = new ArticulosModel();
    }

    public function index() {

        $articulos  = $this->model->getAll();
        $categorias = (new CategoriasModel())->getAll();
        $unidades   = (new UnidadesModel())->getAll();

        return $this->view('operaciones/articulos', [
            'title'      => 'Artículos',
            'articulos'  => $articulos,
            'categorias' => $categorias,
            'unidades'   => $unidades,
            'css'        => ['operaciones/articulos.css'],
            'js'         => ['operaciones/articulos.js'],
        ]);
    }

    public function guardar() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /operaciones/articulos');
            exit;
        }

        $esAjax =
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $id = intval($_POST['id'] ?? 0);

        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $categoria_id = intval($_POST['categoria_id'] ?? 0);
        $unidad_id    = intval($_POST['unidad_id'] ?? 0);
        $precio_compra = floatval($_POST['precio_compra'] ?? 0);
        $precio_venta  = floatval($_POST['precio_venta'] ?? 0);

        if ($codigo === '' || $nombre === '' || $categoria_id <= 0 || $unidad_id <= 0) {
            if ($esAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']);
                exit;
            }
            header('Location: /operaciones/articulos');
            exit;
        }

        try {

            // ✅ EDITAR (update real)
            if ($id > 0) {

                // Si cambió el código, validamos duplicado
                $dup = $this->model->findByCodigo($codigo);
                if ($dup && intval($dup['id']) !== $id) {
                    if ($esAjax) {
                        header('Content-Type: application/json; charset=utf-8');
                        http_response_code(400);
                        echo json_encode(['ok'=>false,'msg'=>'Ya existe un artículo con ese código']);
                        exit;
                    }
                    header('Location: /operaciones/articulos');
                    exit;
                }

                $this->model->update($id, [
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'categoria_id' => $categoria_id,
                    'unidad_id' => $unidad_id,
                    'precio_compra' => $precio_compra,
                    'precio_venta' => $precio_venta,
                ]);

            } else {

                // ✅ NUEVO (o reactivar si existía desactivado)
                $existente = $this->model->findByCodigo($codigo);

                if ($existente) {

                    if ((int)$existente['activo'] === 0) {
                        $this->model->reactivar($existente['id'], [
                            'nombre' => $nombre,
                            'categoria_id' => $categoria_id,
                            'unidad_id' => $unidad_id,
                            'precio_compra' => $precio_compra,
                            'precio_venta' => $precio_venta,
                        ]);
                    } else {
                        // Ya existe activo
                        if ($esAjax) {
                            header('Content-Type: application/json; charset=utf-8');
                            http_response_code(400);
                            echo json_encode(['ok'=>false,'msg'=>'El artículo ya existe']);
                            exit;
                        }
                    }

                } else {

                    $this->model->create(
                        $codigo,
                        $nombre,
                        $categoria_id,
                        $unidad_id,
                        $precio_compra,
                        $precio_venta
                    );
                }
            }

            if ($esAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'ok' => true,
                    'codigo' => $codigo,
                    'articulo' => $this->model->buscarPorCodigo($codigo)
                ]);
                exit;
            }

            header('Location: /operaciones/articulos');
            exit;

        } catch (Throwable $e) {

            if ($esAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(500);
                echo json_encode(['ok'=>false,'msg'=>'Error al guardar artículo']);
                exit;
            }

            header('Location: /operaciones/articulos');
            exit;
        }
    }

    // ✅ Alias para que coincida con tu compras.js: /operaciones/articulos/buscar?codigo=XXXX
    public function buscar() {
        return $this->buscarPorCodigo();
    }

    public function buscarPorCodigo() {

        header('Content-Type: application/json; charset=utf-8');

        if (!isset($_GET['codigo'])) {
            echo json_encode(null);
            return;
        }

        $q = trim((string)$_GET['codigo']);
        if ($q === '') {
            echo json_encode(null);
            return;
        }

        // ✅ Si es PURO NÚMERO => buscar por código (scanner)
        // ✅ Si trae LETRAS (o no es puro número) => buscar por nombre/descripcion
        if (ctype_digit($q)) {
            $articulo = $this->model->buscarPorCodigo($q);
            echo json_encode($articulo ?: null);
            return;
        }

        // modo búsqueda por nombre
        $articulo = $this->model->buscarPorNombre($q);
        echo json_encode($articulo ?: null);
        return;
    }

    public function eliminar() {

        if (!isset($_GET['id'])) {
            header('Location: /operaciones/articulos');
            exit;
        }

        $this->model->toggle($_GET['id'], 0);
        header('Location: /operaciones/articulos');
        exit;
    }

    public function toggle() {

        $this->model->toggle($_GET['id'], $_GET['estado']);
        header('Location: /operaciones/articulos');
        exit;
    }
}
