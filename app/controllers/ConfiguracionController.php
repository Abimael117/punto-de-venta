<?php

class ConfiguracionController extends Controller {

    public function index() {

        return $this->view('configuracion', [
            'title' => 'Configuración',
            'css'   => ['configuracion.css']
        ]);
    }

}
