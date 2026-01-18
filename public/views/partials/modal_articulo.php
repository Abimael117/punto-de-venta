<div id="modalAgregarArticulo" class="modal" style="display:none;">
    <div class="modal-window">

        <div class="modal-header">
            Agregar artículo
        </div>

        <form
            id="formAgregarArticulo"
            method="POST"
            action="/operaciones/articulos/guardar"
            class="modal-body"
        >

            <div class="form-group">
                <label>Código</label>
                <input type="text" name="codigo" required>
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="nombre" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="categoria_id" required>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>">
                                <?= htmlspecialchars($c['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Unidad</label>
                    <select name="unidad_id" required>
                        <?php foreach ($unidades as $u): ?>
                            <?php
                                $abbr = strtolower(trim($u['abreviatura'] ?? ''));
                                $sel  = ($abbr === 'pza') ? 'selected' : '';
                            ?>
                            <option value="<?= $u['id'] ?>" <?= $sel ?>>
                                <?= htmlspecialchars($u['abreviatura']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row row-precios">
                <div class="form-group">
                    <label>Precio compra</label>
                    <input type="number" step="0.01" name="precio_compra">
                </div>

                <div class="form-group">
                    <label>Precio venta</label>
                    <input type="number" step="0.01" name="precio_venta">
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn primary">💾 Guardar</button>
                <button
                    type="button"
                    class="btn danger"
                    id="btnCerrarModalArticulo"
                >
                    ❌ Cancelar
                </button>
            </div>

        </form>
    </div>
</div>
