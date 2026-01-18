<div class="role-container">

    <h1 class="role-title">Roles</h1>

    <form action="/configuracion/roles/guardar" method="POST" class="role-form">
        <input type="text" name="nombre" placeholder="Nombre del rol (Ej. Cajero)" required>
        <button type="submit">Guardar</button>
    </form>

    <table class="role-table">
        <thead>
            <tr>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $rol): ?>
                <tr>
                    <td><?= htmlspecialchars($rol['nombre']) ?></td>
                    <td>
                        <span class="role-badge <?= $rol['activo'] ? 'activo' : 'inactivo' ?>">
                            <?= $rol['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td>
                        <a class="role-action"
                           href="/configuracion/roles/toggle?id=<?= $rol['id'] ?>&estado=<?= $rol['activo'] ? 0 : 1 ?>">
                            <?= $rol['activo'] ? 'Desactivar' : 'Activar' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
