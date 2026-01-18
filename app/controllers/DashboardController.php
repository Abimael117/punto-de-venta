<?php

require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController extends Controller {

    private $model;

    public function __construct() {
        AuthMiddleware::check(); // Solo usuarios logueados
        $this->model = new DashboardModel();
    }

    public function index() {

        return $this->view('dashboard', [
            'title' => 'Dashboard',
            'user'  => $_SESSION['user'],
            'css'   => ['dashboard.css'],
            'js'    => ['dashboard.js'] // <-- agrega tu JS del dashboard
        ]);
    }

    // GET /dashboard/data?mode=week|month|year
    public function data() {
        header('Content-Type: application/json; charset=utf-8');

        $mode = $_GET['mode'] ?? 'week';
        if (!in_array($mode, ['week','month','year'], true)) $mode = 'week';

        try {
            $chart = $this->model->getVentasChart($mode);

            $alerts = [
                'stock' => $this->model->getAlertStockBajo(5),
                'cxc'   => $this->model->getAlertCxcVencidas(5),
                'corte' => $this->model->getAlertCortePendiente(),
            ];

            echo json_encode([
                'ok'   => true,
                'data' => [
                    'chart'  => $chart,
                    'alerts' => $alerts
                ]
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'ok'  => false,
                'msg' => 'Error al cargar dashboard',
                'err' => $e->getMessage()
            ]);
        }
    }
}