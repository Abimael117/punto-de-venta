<div class="unit-container">

    <h1 class="unit-title">Unidades</h1>

    <form action="/configuracion/unidades/guardar" method="POST" class="unit-form">
        <input type="text" name="nombre" placeholder="Nombre (Ej. Pieza)" required>
        <input type="text" name="abreviatura" placeholder="Abrev. (Ej. pza)" required>
        <button type="submit">Guardar</button>
    </form>

    <div class="unit-card">
        <table class="unit-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Abreviatura</th>
                    <th>Estado</th>
                    <th class="center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($unidades as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                        <td><?= htmlspecialchars($u['abreviatura']) ?></td>
                        <td>
                            <span class="badge <?= $u['activo'] ? 'badge-active' : 'badge-inactive' ?>">
                                <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td class="center">
                            <a class="action-link"
                               href="/configuracion/unidades/toggle?id=<?= $u['id'] ?>&estado=<?= $u['activo'] ? 0 : 1 ?>">
                                <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
