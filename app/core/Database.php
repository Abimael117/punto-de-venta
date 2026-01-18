<?php

class Database {

    private static $instance = null;

    public static function connect() {

        if (self::$instance === null) {

            $host = '127.0.0.1';
            $dbname = 'pos';
            $user = 'root';
            $pass = '';

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            self::$instance = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $user,
                $pass,
                $options
            );
        }

        return self::$instance;
    }
}
