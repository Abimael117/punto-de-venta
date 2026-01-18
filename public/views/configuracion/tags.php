<div class="tag-container">

    <h1 class="tag-title">Tags</h1>

    <form action="/configuracion/tags/guardar" method="POST" class="tag-form">
        <input type="text" name="nombre" placeholder="Nombre del tag (Ej. Oferta)" required>
        <button type="submit">Guardar</button>
    </form>

    <table class="tag-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tags as $tag): ?>
                <tr>
                    <td><?= htmlspecialchars($tag['nombre']) ?></td>
                    <td>
                        <span class="badge <?= $tag['activo'] ? 'badge-success' : 'badge-danger' ?>">
                            <?= $tag['activo'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </td>
                    <td>
                        <a class="btn-action"
                           href="/configuracion/tags/toggle?id=<?= $tag['id'] ?>&estado=<?= $tag['activo'] ? 0 : 1 ?>">
                            <?= $tag['activo'] ? 'Desactivar' : 'Activar' ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
