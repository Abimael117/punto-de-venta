<?php

class AuthMiddleware {

    public static function check() {

        if (!isset($_SESSION['user'])) {
            header("Location: /login");
            exit;
        }
    }
}
