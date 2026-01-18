<div class="emp-container">

    <h1 class="emp-title">Empleados</h1>

    <form action="/configuracion/empleados/guardar" method="POST" class="emp-form">
        <input type="text" name="nombre" placeholder="Nombre del empleado" required>
        <input type="text" name="telefono" placeholder="Teléfono">
        <button type="submit">Guardar</button>
    </form>

    <div class="emp-table-wrapper">
        <table class="emp-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e['nombre']) ?></td>
                        <td><?= htmlspecialchars($e['telefono']) ?></td>
                        <td>
                            <span class="status <?= $e['activo'] ? 'active' : 'inactive' ?>">
                                <?= $e['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td>
                            <a class="action-link"
                               href="/configuracion/empleados/toggle?id=<?= $e['id'] ?>&estado=<?= $e['activo'] ? 0 : 1 ?>">
                                <?= $e['activo'] ? 'Desactivar' : 'Activar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
