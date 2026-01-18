<div class="moneda-container">

    <h1 class="moneda-title">Moneda</h1>

    <form action="/configuracion/moneda/guardar" method="POST" class="moneda-form">
        <input type="text" name="nombre" placeholder="Nombre (Ej. Peso Mexicano)" required>
        <input type="text" name="simbolo" placeholder="Símbolo ($, €, Q)" required>
        <button type="submit">Guardar</button>
    </form>

    <table class="moneda-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Símbolo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($monedas as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['nombre']) ?></td>
                    <td><?= htmlspecialchars($m['simbolo']) ?></td>
                    <td>
                        <span class="badge <?= $m['activo'] ? 'active' : 'inactive' ?>">
                            <?= $m['activo'] ? 'Activa' : 'Inactiva' ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!$m['activo']): ?>
                            <a href="/configuracion/moneda/activar?id=<?= $m['id'] ?>" class="btn-activate">
                                Activar
                            </a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
