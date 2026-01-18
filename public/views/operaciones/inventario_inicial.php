<div class="invini-page"> 

  <h2 class="module-title">Inventario Inicial</h2>

  <div class="invini-toolbar">
    <button class="btn primary" id="btnGuardar">💾 Guardar (F3)</button>
    <button class="btn danger" id="btnCancelar">❌ Cancelar (ESC)</button>
  </div>

  <div class="invini-card">

    <div class="invini-top">
      <div class="invini-search">
        <span>🔎</span>
        <input id="codigoArticulo" placeholder="Escanea / escribe código..." autocomplete="off">
      </div>

      <div class="invini-meta">
        <div class="field">
          <label>Fecha</label>
          <input type="datetime-local" id="invFecha">
        </div>
        <div class="field">
          <label>Notas</label>
          <input type="text" id="invNotas" placeholder="Opcional...">
        </div>
      </div>
    </div>

    <table class="module-table" id="tablaInvIni">
      <thead>
        <tr>
          <th>Código</th>
          <th>Descripción</th>
          <th style="text-align:right;">Existencia actual</th>
          <th style="text-align:right;">Cantidad inicial</th>
          <th style="text-align:right;">Importe</th>
          <th style="width:140px;">Acción</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <div class="invini-kpis">
      <div class="invini-kpi"><span>Artículos</span><strong id="kpiItems">0</strong></div>
      <div class="invini-kpi"><span>Total</span><strong id="kpiTotal">$0.00</strong></div>
    </div>

  </div>
</div>

<!-- =========================
     MODAL: CANTIDAD
========================= -->
<div class="modal" id="modalInvIni" style="display:none;">
  <div class="modal-window" style="width:780px; max-width:96%;">
    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:18px;">📦</span>
        <span>Capturar inventario inicial</span>
      </div>
      <button class="prov-close" id="btnCerrarInvIni" type="button">✕</button>
    </div>

    <div class="modal-body">
      <div class="invini-detail">
        <div class="cc-pill"><span>Código</span><strong id="mCodigo">—</strong></div>
        <div class="cc-pill"><span>Descripción</span><strong id="mDesc">—</strong></div>
        <div class="cc-pill"><span>Existencia actual</span><strong id="mStock">0.00</strong></div>
      </div>

      <div class="invini-paybox">
        <div class="field">
          <label>Cantidad inicial</label>
          <input type="number" step="0.01" id="mCantidad" placeholder="0.00">
        </div>

        <div class="cc-actions">
          <button class="btn primary" id="btnAplicarInvIni">✅ Aplicar</button>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn secondary" id="btnCerrarInvIni2" type="button">Cerrar</button>
    </div>
  </div>
</div>

<!-- =========================
     MODAL: AGREGAR ARTICULO (SIN SALIR)
========================= -->
<div class="modal" id="modalInvAddArt" style="display:none;">
  <div class="modal-window inv-addart-window">
    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:18px;">➕</span>
        <span>Agregar artículo</span>
      </div>
      <button class="prov-close" id="btnCerrarInvAddArt" type="button">✕</button>
    </div>

    <div class="modal-body">
      <div class="inv-addart-banner">
        <div class="inv-addart-banner-title">Artículo no encontrado</div>
        <div class="inv-addart-banner-sub">Créalo aquí mismo y lo agregamos al Inventario Inicial sin salir de la pantalla.</div>
      </div>

      <form id="formInvAddArt" autocomplete="off">
        <div class="inv-form-grid">

          <div class="inv-field">
            <label>Código *</label>
            <input type="text" name="codigo" id="ia_codigo" required>
            <small>Tip: si lo escaneaste, ya viene listo.</small>
          </div>

          <div class="inv-field">
            <label>Descripción *</label>
            <input type="text" name="nombre" id="ia_nombre" placeholder="Ej. Coca cola 600ml" required>
          </div>

          <div class="inv-2col">
            <div class="inv-field">
              <label>Categoría *</label>
              <select name="categoria_id" id="ia_categoria" required>
                <option value="">-- Selecciona --</option>
                <?php if (!empty($categorias)): ?>
                  <?php foreach ($categorias as $c): ?>
                    <?php
                      $nomCat = strtolower(trim($c['nombre'] ?? ''));
                      // ✅ coincide exacto "abarrotes"
                      $isAbarrotes = ($nomCat === 'abarrotes');
                    ?>
                    <option value="<?= $c['id'] ?>" <?= $isAbarrotes ? 'selected' : '' ?>>
                      <?= htmlspecialchars($c['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>

            <div class="inv-field">
              <label>Unidad *</label>
              <select name="unidad_id" id="ia_unidad" required>
                <option value="">-- Selecciona --</option>
                <?php if (!empty($unidades)): ?>
                  <?php foreach ($unidades as $u): ?>
                    <?php
                      $labUni = strtolower(trim($u['abreviatura'] ?? $u['nombre'] ?? ''));
                      // ✅ coincide exacto "pza"
                      $isPza = ($labUni === 'pza');
                    ?>
                    <option value="<?= $u['id'] ?>" <?= $isPza ? 'selected' : '' ?>>
                      <?= htmlspecialchars($u['abreviatura'] ?? $u['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <div class="inv-2col">
            <div class="inv-field">
              <label>Precio compra</label>
              <input type="number" step="0.01" name="precio_compra" id="ia_precio_compra" value="0">
            </div>
            <div class="inv-field">
              <label>Precio venta</label>
              <input type="number" step="0.01" name="precio_venta" id="ia_precio_venta" value="0">
            </div>
          </div>

        </div>

        <div class="inv-addart-footer">
          <button type="submit" class="btn primary" id="btnInvAddArtGuardar">💾 Guardar</button>
          <button type="button" class="btn secondary" id="btnInvAddArtCancelar">❌ Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="/public/assets/js/operaciones/inventario_inicial.js"></script>
