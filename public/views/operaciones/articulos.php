<div class="toolbar">
    <button class="btn primary" id="btnAgregar">➕ Agregar (F3)</button>
    <button class="btn" id="btnEditar">✏️ Editar (F4)</button>
    <button class="btn" id="btnRecargar">🔄 Recargar (F5)</button>
    <button class="btn danger" id="btnEliminar">❌ Eliminar (F6)</button>
</div>

<div class="articulos-layout">

    <!-- LISTADO -->
    <div class="listado">

        <div class="buscador">
            <input
                type="text"
                id="buscarArticulo"
                placeholder="Buscar por código o descripción"
                autocomplete="off"
            >
            <button type="button">🔍</button>
        </div>

        <table class="tabla-articulos">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Exist.</th>
                    <th>Precio</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
            <?php if (!empty($articulos)): ?>
                <?php foreach ($articulos as $a): ?>
                    <tr
                        data-id="<?= (int)$a['id'] ?>"
                        data-codigo="<?= htmlspecialchars($a['codigo']) ?>"
                        data-nombre="<?= htmlspecialchars($a['nombre']) ?>"
                        data-stock="<?= (float)($a['stock'] ?? 0) ?>"
                        data-precio_compra="<?= (float)($a['precio_compra'] ?? 0) ?>"
                        data-precio_venta="<?= (float)($a['precio_venta'] ?? 0) ?>"
                        data-categoria_id="<?= (int)($a['categoria_id'] ?? 0) ?>"
                        data-unidad_id="<?= (int)($a['unidad_id'] ?? 0) ?>"
                    >
                        <td><?= htmlspecialchars($a['codigo']) ?></td>
                        <td><?= htmlspecialchars($a['nombre']) ?></td>
                        <td><?= number_format((float)($a['stock'] ?? 0), 2) ?></td>
                        <td>$<?= number_format((float)($a['precio_venta'] ?? 0), 2) ?></td>
                        <td class="acciones">⋮</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="tabla-vacia">
                        No hay artículos registrados
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

    </div>

    <!-- PANEL DERECHO -->
    <div class="detalle">
        <h3>Artículo seleccionado</h3>

        <div class="preview">
            📷
        </div>

        <div class="info">
            <p><strong>Código:</strong> —</p>
            <p><strong>Nombre:</strong> —</p>
            <p><strong>Existencia:</strong> —</p>
            <p><strong>Precio:</strong> —</p>
        </div>
    </div>

</div>

<!-- Selects ocultos para el modal -->
<select id="selectCategorias" hidden>
    <?php foreach ($categorias as $c): ?>
        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
    <?php endforeach; ?>
</select>

<select id="selectUnidades" hidden>
    <?php foreach ($unidades as $u): ?>
        <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['abreviatura']) ?></option>
    <?php endforeach; ?>
</select>

<script src="/public/assets/js/operaciones/articulos.js"></script>
