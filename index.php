<?php
date_default_timezone_set('America/Mexico_City');
session_start();

require __DIR__ . '/app/core/Database.php';
require __DIR__ . '/app/core/Controller.php';
require __DIR__ . '/app/core/Helpers.php';
require __DIR__ . '/app/middleware/AuthMiddleware.php';
require __DIR__ . '/app/middleware/RolMiddleware.php';

$routes = require __DIR__ . '/app/routes/web.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (isset($routes[$uri])) {

    $controllerPath = $routes[$uri]['controller'];
    $action = $routes[$uri]['action'];

    // Ruta completa al archivo del controller
    $file = __DIR__ . "/app/controllers/$controllerPath.php";

    if (!file_exists($file)) {
        die("Controller no encontrado: $file");
    }

    require $file;

    // Obtener solo el nombre de la clase (sin carpetas)
    $className = basename($controllerPath);

    $controller = new $className();

    if (!method_exists($controller, $action)) {
        die("Acción no encontrada: $action");
    }

    $controller->$action();

} else {
    echo "404 | Página no encontrada: $uri";
}
