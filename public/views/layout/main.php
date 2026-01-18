<?php
// $title   → Título dinámico
// $content → Contenido de cada vista
// $user    → Usuario logueado
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? 'POS' ?></title>

    <!-- CSS principal -->
    <link rel="stylesheet" href="/public/assets/css/style.css">

    <?php if (!empty($cssFiles)): ?>
        <?php foreach ($cssFiles as $css): ?>
            <link rel="stylesheet" href="/public/assets/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>

</head>

<body>

    <!-- NAVBAR SUPERIOR ESTILO SICAR -->
    <nav class="topmenu">

        <div class="menu-item" onclick="location.href='/operaciones'">
            <span class="menu-ico">💵</span>
            <span>Operaciones</span>
        </div>

        <div class="menu-item" onclick="location.href='/consultas'">
            <span class="menu-ico">🔍</span>
            <span>Consultas</span>
        </div>

        <div class="menu-item" onclick="location.href='/procesos'">
            <span class="menu-ico">⚙️</span>
            <span>Procesos</span>
        </div>

        <div class="menu-item" onclick="location.href='/reportes'">
            <span class="menu-ico">📊</span>
            <span>Reportes</span>
        </div>

        <div class="menu-item" onclick="location.href='/estadisticas'">
            <span class="menu-ico">📈</span>
            <span>Estadísticas</span>
        </div>

        <div class="menu-item" onclick="location.href='/configuracion'">
            <span class="menu-ico">🛠️</span>
            <span>Config.</span>
        </div>

        <!-- ✅ USUARIO (DERECHA) CON MENÚ -->
        <div class="user-box" id="topUser">
            <span class="menu-ico">👤</span>
            <span><?= htmlspecialchars($user['nombre'] ?? 'Invitado') ?></span>
            <span class="user-caret">▾</span>

            <div class="user-menu" id="topUserMenu">

                <!-- 🏠 Dashboard -->
                <a class="user-item" href="/">
                    🏠 Dashboard
                </a>

                <div class="user-divider"></div>

                <!-- 🚪 Cerrar sesión -->
                <a class="user-item danger" href="/logout" onclick="return confirm('¿Cerrar sesión?');">
                    🚪 Cerrar sesión
                </a>
            </div>
        </div>


    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-container">
        <?= $content ?>
    </main>

    <footer class="footer">
        <p>© <?= date('Y') ?> Mi POS — Todos los derechos reservados.</p>
    </footer>

    <!-- ✅ JS del menú (simple y robusto) -->
    <script>
      (function(){
        const wrap = document.getElementById('topUser');
        const menu = document.getElementById('topUserMenu');
        if (!wrap || !menu) return;

        function close(){ menu.style.display = 'none'; }
        function toggle(){
          const isOpen = menu.style.display === 'block';
          menu.style.display = isOpen ? 'none' : 'block';
        }

        wrap.addEventListener('click', (e) => {
          e.stopPropagation();
          toggle();
        });

        document.addEventListener('click', () => close());
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
      })();
    </script>

</body>
</html>
