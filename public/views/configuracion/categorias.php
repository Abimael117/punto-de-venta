<div class="cat-container">

    <h1 class="cat-title">Categorías</h1>

    <form action="/configuracion/categorias/guardar" method="POST" class="cat-form">
        <input type="hidden" name="id" id="cat-id">
        <input
            type="text"
            name="nombre"
            id="cat-nombre"
            placeholder="Nombre de la categoría"
            required
        >
        <button type="submit">Guardar</button>
    </form>

    <table class="cat-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th class="center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categorias)): ?>
                <tr>
                    <td colspan="3" class="cat-empty">
                        No hay categorías registradas
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?= htmlspecialchars($cat['nombre']) ?></td>

                        <td>
                            <span class="cat-status <?= $cat['activo'] ? 'activo' : 'inactivo' ?>">
                                <?= $cat['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>

                        <td class="center">
                            <a
                                href="/configuracion/categorias/toggle?id=<?= $cat['id'] ?>&estado=<?= $cat['activo'] ? 0 : 1 ?>"
                                class="cat-btn <?= $cat['activo'] ? 'danger' : 'success' ?>"
                            >
                                <?= $cat['activo'] ? 'Desactivar' : 'Activar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</div>