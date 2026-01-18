<div class="printer-container">

    <h1 class="printer-title">Impresoras</h1>

    <form action="/configuracion/impresoras/guardar" method="POST" class="printer-form">
        <input
            type="text"
            name="nombre"
            placeholder="Nombre de la impresora (Ej. Ticket Mostrador)"
            required
        >
        <button type="submit">Guardar</button>
    </form>

    <table class="printer-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($impresoras as $imp): ?>
                <tr>
                    <td><?= htmlspecialchars($imp['nombre']) ?></td>
                    <td>
                        <?php if ($imp['activo']): ?>
                            <span class="status active">Activa</span>
                        <?php else: ?>
                            <span class="status inactive">Inactiva</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a
                            class="action-btn <?= $imp['activo'] ? 'off' : 'on' ?>"
                            href="/configuracion/impresoras/toggle?id=<?= $imp['id'] ?>&estado=<?= $imp['activo'] ? 0 : 1 ?>"
                        >
                            <?= $imp['activo'] ? 'Desactivar' : 'Activar' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
