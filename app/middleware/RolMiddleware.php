<?php

class RolMiddleware {

    public static function require($roles = []) {
        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }

        $rolUsuario = $_SESSION['user']['rol'];

        if (!in_array($rolUsuario, $roles)) {
            echo "<h2>🚫 Acceso denegado</h2>";
            echo "No tienes permiso para ver esta sección.";
            exit;
        }
    }
}
