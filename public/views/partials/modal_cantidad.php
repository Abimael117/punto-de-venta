<div id="modalCantidadArticulo" class="modal" style="display:none;">
    <div class="modal-window modal-cantidad">

        <div class="modal-header">
            Detalle de la compra
        </div>

        <div class="modal-body">

            <div class="mc-info">
                <div class="mc-desc" id="mcDescripcion"></div>
                <div class="mc-codigo">
                    Código: <span id="mcCodigo"></span>
                </div>
            </div>

            <div class="mc-grid">

                <div class="form-group">
                    <label>Cantidad</label>
                    <input type="number" id="mcCantidad" min="1" step="1" value="1">
                </div>

                <div class="form-group">
                    <label>Costo (sin impuesto)</label>
                    <input type="number" id="mcCosto" step="0.01">
                </div>

                <div class="form-group full">
                    <label>Impuesto (ej. IEPS 8% / IVA 16%)</label>
                    <select id="mcImpuesto">
                        <option value="" data-tasa="0" data-trasret="TRAS">Sin impuesto</option>
                        <?php if (!empty($impuestos)): ?>
                            <?php foreach ($impuestos as $imp): ?>
                                <option
                                    value="<?= (int)$imp['id'] ?>"
                                    data-tasa="<?= htmlspecialchars($imp['tasa']) ?>"
                                    data-trasret="<?= htmlspecialchars($imp['tras_ret']) ?>"
                                >
                                    <?= htmlspecialchars($imp['nombre']) ?> (<?= rtrim(rtrim(number_format((float)$imp['tasa'],4), '0'), '.') ?>%)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Utilidad %</label>
                    <input type="number" id="mcUtilidad" step="0.01" value="0">
                </div>

                <div class="form-group">
                    <label>Costo real (con impuesto)</label>
                    <input type="number" id="mcCostoReal" step="0.01" readonly>
                </div>

                <div class="form-group">
                    <label>Precio de venta sugerido</label>
                    <input type="number" id="mcPrecioVentaSug" step="0.01" readonly>
                </div>

                <div class="form-group">
                    <label>Precio de venta (editable)</label>
                    <input type="number" id="mcPrecioVenta" step="0.01" placeholder="Precio de venta sugerido">
                </div>

            </div>

            <div class="mc-totales">
                <div class="mc-row">
                    <span>Subtotal:</span>
                    <strong>$ <span id="mcSubtotal">0.00</span></strong>
                </div>
                <div class="mc-row">
                    <span>Impuesto:</span>
                    <strong>$ <span id="mcImpuestoMonto">0.00</span></strong>
                </div>
                <div class="mc-row total">
                    <span>Importe:</span>
                    <strong>$ <span id="mcImporte">0.00</span></strong>
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <button class="btn danger" id="btnCancelarCantidad">❌ Cancelar</button>
            <button class="btn primary" id="btnAceptarCantidad">✔ Aceptar</button>
        </div>

    </div>
</div>
