<div class="cp-container">

    <h1 class="cp-title">Impresoras por Caja</h1>

    <form action="/configuracion/caja-impresora/asignar" method="POST" class="cp-form">

        <select name="caja_id" required>
            <option value="">Seleccione caja</option>
            <?php foreach ($cajas as $caja): ?>
                <option value="<?= $caja['id'] ?>">
                    <?= htmlspecialchars($caja['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="impresora_id" required>
            <option value="">Seleccione impresora</option>
            <?php foreach ($impresoras as $imp): ?>
                <option value="<?= $imp['id'] ?>">
                    <?= htmlspecialchars($imp['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Asignar</button>
    </form>

    <table class="cp-table">
        <thead>
            <tr>
                <th>Caja</th>
                <th>Impresora</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($asignaciones as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['caja']) ?></td>
                    <td><?= htmlspecialchars($a['impresora']) ?></td>
                    <td>
                        <?php if ($a['activo']): ?>
                            <span class="status active">Activo</span>
                        <?php else: ?>
                            <span class="status inactive">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a
                            class="action-btn <?= $a['activo'] ? 'off' : 'on' ?>"
                            href="/configuracion/caja-impresora/toggle?id=<?= $a['id'] ?>&estado=<?= $a['activo'] ? 0 : 1 ?>"
                        >
                            <?= $a['activo'] ? 'Desactivar' : 'Activar' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
