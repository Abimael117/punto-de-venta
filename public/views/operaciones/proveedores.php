<div class="module-container">

    <h2 class="module-title">Proveedores</h2>

    <form method="POST" action="/operaciones/proveedores/guardar" class="form-row">
        <input type="text" name="nombre" placeholder="Nombre del proveedor" required>
        <input type="text" name="telefono" placeholder="Teléfono">
        <input type="email" name="email" placeholder="Email">
        <button type="submit" class="btn-primary">Guardar</button>
    </form>

    <div class="table-card">
        <table class="module-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proveedores as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td><?= htmlspecialchars($p['telefono']) ?></td>
                        <td><?= htmlspecialchars($p['email']) ?></td>
                        <td>
                            <?php if ($p['activo']): ?>
                                <span class="badge active">Activo</span>
                            <?php else: ?>
                                <span class="badge inactive">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a
                                class="action-link <?= $p['activo'] ? '' : 'activate' ?>"
                                href="/operaciones/proveedores/toggle?id=<?= $p['id'] ?>&estado=<?= $p['activo'] ? 0 : 1 ?>"
                            >
                                <?= $p['activo'] ? 'Desactivar' : 'Activar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
