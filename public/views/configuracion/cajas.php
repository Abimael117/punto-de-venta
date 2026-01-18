<div class="box-container">

    <h1 class="box-title">Cajas</h1>

    <form action="/configuracion/cajas/guardar" method="POST" class="box-form">
        <input
            type="text"
            name="nombre"
            placeholder="Nombre de la caja (Ej. Caja Mostrador)"
            required
        >
        <button type="submit">Guardar</button>
    </form>

    <div class="box-card">
        <table class="box-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cajas)): ?>
                    <?php foreach ($cajas as $caja): ?>
                        <tr>
                            <td><?= htmlspecialchars($caja['nombre']) ?></td>
                            <td>
                                <span class="status <?= $caja['activo'] ? 'activo' : 'inactivo' ?>">
                                    <?= $caja['activo'] ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                            <td>
                                <a
                                    class="action-link"
                                    href="/configuracion/cajas/toggle?id=<?= $caja['id'] ?>&estado=<?= $caja['activo'] ? 0 : 1 ?>"
                                >
                                    <?= $caja['activo'] ? 'Desactivar' : 'Activar' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="empty-row">
                            No hay cajas registradas
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
