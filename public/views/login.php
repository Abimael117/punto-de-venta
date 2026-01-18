<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login POS</title>
    <link rel="stylesheet" href="/public/assets/css/login.css">

    <!-- Bootstrap icons para decorar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="login-body">

<div class="login-card">

    <div class="login-header">
        <i class="bi bi-person-circle"></i>
        <h2>Iniciar sesión</h2>
    </div>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="/login/post" method="POST">

        <div class="input-group">
            <label><i class="bi bi-person-fill"></i> Usuario</label>
            <input type="text" name="usuario" autocomplete="off">
        </div>

        <div class="input-group">
            <label><i class="bi bi-lock-fill"></i> Contraseña</label>
            <input type="password" name="password">
        </div>

        <button type="submit">
            <i class="bi bi-box-arrow-in-right"></i> Entrar
        </button>
    </form>

</div>

</body>
</html>
