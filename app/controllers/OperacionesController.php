<?php

class OperacionesController extends Controller {

    public function index() {
        return $this->view('operaciones', [
            'title' => 'Operaciones',
            'css' => ['operaciones.css']
        ]);
    }
}
