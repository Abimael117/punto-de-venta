<?php

class ConsultasController extends Controller {

    public function __construct() {
        AuthMiddleware::check();
    }

    // GET /consultas
    public function index() {
        return $this->view('consultas', [
            'title' => 'Consultas',
            'css'   => ['consultas.css'],
            'js'    => ['consultas.js']
        ]);
    }

    // (placeholders para las siguientes pantallas)
    // GET /consultas/ventas
    public function ventas() {
        return $this->view('consultas/ventas', [
            'title' => 'Consultas - Ventas',
            'css'   => ['consultas.css'],
            'js'    => ['consultas_ventas.js']
        ]);
    }

    // GET /consultas/ventas-credito
    public function ventasCredito() {
        return $this->view('consultas/ventas_credito', [
            'title' => 'Consultas - Ventas a Crédito',
            'css'   => ['consultas.css'],
            'js'    => ['consultas_ventas_credito.js']
        ]);
    }

    // GET /consultas/ventas-detalle
    public function ventasDetalle() {
        return $this->view('consultas/ventas_detalle', [
            'title' => 'Consultas - Detalle de Ventas',
            'css'   => ['consultas.css'],
            'js'    => ['consultas_ventas_detalle.js']
        ]);
    }

    // GET /consultas/compras
    public function compras() {
        return $this->view('consultas/compras', [
            'title' => 'Consultas - Compras',
            'css'   => ['consultas.css'],
            'js'    => ['consultas_compras.js']
        ]);
    }

    // GET /consultas/compras-credito
    public function comprasCredito() {
        return $this->view('consultas/compras_credito', [
            'title' => 'Consultas - Compras a Crédito',
            'css'   => ['consultas.css'],
            'js'    => ['consultas_compras_credito.js']
        ]);
    }

    // GET /consultas/inventario
    public function inventario() {
        return $this->view('consultas/inventario', [
            'title' => 'Consultas - Movimientos de Inventario',
            'css'   => ['consultas.css'],
            'js'    => ['consultas_inventario.js']
        ]);
    }
}
