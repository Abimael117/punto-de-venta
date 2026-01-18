<?php

class Controller {

    protected function view($view, $data = [], $useLayout = true) {

        extract($data);

        // Renderizamos la vista en $content
        ob_start();
        require __DIR__ . "/../../public/views/$view.php";
        $content = ob_get_clean();

        // Esto añade soporte para CSS por vista
        $cssFiles = $data['css'] ?? [];

        if ($useLayout) {
            $user = $_SESSION['user'] ?? null;
            require __DIR__ . "/../../public/views/layout/main.php";
        } else {
            echo $content;
        }
    }
}
