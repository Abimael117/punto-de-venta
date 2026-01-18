<div class="user-container">

    <h1 class="user-title">Usuarios</h1>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert-success">
            <?= $_SESSION['flash']; unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <form action="/configuracion/usuarios/guardar" method="POST" class="user-form">

        <input type="text" name="nombre" placeholder="Nombre completo" required>
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>

        <select name="rol" required>
            <option value="">Rol</option>
            <option value="admin">Administrador</option>
            <option value="cajero">Cajero</option>
        </select>

        <button type="submit">Guardar</button>
    </form>

    <div class="user-table-wrapper">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                        <td><?= htmlspecialchars($u['usuario']) ?></td>
                        <td><?= ucfirst($u['rol']) ?></td>
                        <td>
                            <span class="badge <?= $u['activo'] ? 'badge-success' : 'badge-danger' ?>">
                                <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td>
                            <a class="action-link"
                               href="/configuracion/usuarios/toggle?id=<?= $u['id'] ?>&estado=<?= $u['activo'] ? 0 : 1 ?>">
                                <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
