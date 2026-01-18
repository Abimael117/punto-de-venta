<?php

class AuthController extends Controller {

    public function loginView() {

        // Si ya está logueado → dashboard
        if (isset($_SESSION['user'])) {
            redirect('/');
        }

        return $this->view('login', [], false); // ⬅ SIN LAYOUT
    }

    public function login() {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
        }

        $usuario = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($usuario === '' || $password === '') {
            return $this->view('login', [
                'error' => 'Por favor llena todos los campos.'
            ], false); // ⬅ SIN LAYOUT
        }

        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE usuario = ? LIMIT 1");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->view('login', [
                'error' => 'Usuario o contraseña incorrectos.'
            ], false); // ⬅ SIN LAYOUT
        }

        // GUARDAR SESIÓN 🔥
        $_SESSION['user'] = [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'usuario' => $user['usuario'],
            'rol' => $user['rol']
        ];

        redirect('/');
    }

    public function logout() {
        session_destroy();
        redirect('/login');
    }
}
