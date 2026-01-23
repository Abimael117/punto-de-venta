<div class="compras-page">

  <!-- Header / Title -->
  <div class="compras-header">
    <h2 class="module-title">Compras</h2>

    <div class="compras-actions">
      <button type="button" class="btn primary" id="btnGuardar">💾 Guardar (F3)</button>
      <button type="button" class="btn secondary" id="btnCancelar">❌ Cancelar (ESC)</button>
      <button type="button" class="btn danger" id="btnRemover" title="Remover artículo (DEL)">🗑 Remover (DEL)</button>
    </div>
  </div>

  <!-- =========================
       SUPER CARD (TODO JUNTO)
  ========================= -->
  <div class="compras-shell">

    <!-- CABECERA (datos compra) -->
    <div class="compras-section">
      <div class="section-head">
        <div class="section-title">
          <span class="section-dot"></span>
        </div>
      </div>

      <div class="compras-head">
        <div class="head-grid">

          <!-- ❌ Folio fuera (es automático y no aporta) -->

          <div class="field">
            <label>Fecha y hora</label>
            <input type="datetime-local" id="compraFechaHora">
          </div>

          <div class="field">
            <label>Tipo</label>
            <select id="compraTipo">
              <option value="CONTADO">CONTADO</option>
              <option value="CREDITO">CRÉDITO</option>
            </select>
          </div>

          <div class="field">
            <label>Método de pago</label>
            <select id="compraMetodo">
              <option value="EFECTIVO">EFECTIVO</option>
              <option value="TARJETA">TARJETA</option>
              <option value="TRANSFERENCIA">TRANSFERENCIA</option>
            </select>
          </div>

          <div class="field check">
            <label>Pagada con caja</label>
            <div class="check-row">
              <input type="checkbox" id="compraConCaja">
              <span>Solo si salió dinero de la caja</span>
            </div>
          </div>

          <!--
          <div class="field full">
            <label>Notas</label>
            <input type="text" id="compraNotas" placeholder="Opcional: factura, remisión, referencia...">
          </div>
          -->
        </div>
      </div>
    </div>

    <div class="section-divider"></div>

    <!-- CAPTURA (proveedor + código) -->
    <div class="compras-section">
      <div class="section-head">
        <div class="section-title">
          <span class="section-dot"></span>
        </div>
      </div>

      <div class="compras-captura-card">
        <div class="form-row">

          <div class="field">
            <label>Proveedor</label>

            <div class="prov-picker">
              <button type="button" class="prov-icon" id="btnAbrirProveedores" title="Seleccionar proveedor (F2)">🛒</button>

              <input
                type="text"
                id="proveedorTexto"
                placeholder="Selecciona proveedor (F2)"
                autocomplete="off"
                readonly
              >
              <input type="hidden" id="proveedorId">
            </div>

            <select id="proveedorCompra" hidden>
              <option value="">-- Selecciona proveedor --</option>
              <?php foreach ($proveedores as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>Código de artículo</label>
            <div class="scan-wrap">
              <input
                type="text"
                id="codigoArticulo"
                placeholder="Escanea o escribe el código del artículo"
                autofocus
              >
              <span class="scan-hint">Enter para agregar</span>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="section-divider"></div>

    <!-- DETALLE (tabla + totales) -->
    <div class="compras-section">
      <div class="section-head section-head-row">
        <div>
          <div class="section-title">
            <span class="section-dot"></span>
          </div>
        </div>

        <div class="totales-inline">
          <div class="total-mini">
            <span>Subtotal</span>
            <strong>$<span id="subtotalCompra">0.00</span></strong>
          </div>
          <div class="total-mini">
            <span>Impuestos</span>
            <strong>$<span id="impuestosCompra">0.00</span></strong>
          </div>
          <div class="total-pill">
            <span>Total</span>
            <strong>$<span id="totalCompra">0.00</span></strong>
          </div>
        </div>
      </div>

      <div class="table-wrap">
        <table class="module-table tabla-detalle">
          <thead>
            <tr>
              <th>Código</th>
              <th>Descripción</th>
              <th style="text-align:right;">Cant.</th>
              <th style="text-align:right;">Costo</th>
              <th style="text-align:right;">Importe</th>
            </tr>
          </thead>
          <tbody id="detalleCompra"></tbody>
        </table>
      </div>
    </div>

  </div><!-- /compras-shell -->

  <?php require __DIR__ . '/../partials/modal_articulo.php'; ?>
  <?php require __DIR__ . '/../partials/modal_cantidad.php'; ?>

  <!-- =========================
       MODAL PROVEEDORES
  ========================= -->
  <div id="modalProveedores" class="modal" style="display:none;">
    <div class="modal-window prov-modal">

      <div class="modal-header">
        <div style="display:flex; align-items:center; gap:10px;">
          <span style="font-size:18px;">🛒</span>
          <span>Seleccionar Proveedor</span>
        </div>
        <button type="button" class="prov-close" id="btnCerrarProveedores">✖</button>
      </div>

      <div class="modal-body">

        <div class="prov-topbar">
          <div class="prov-search">
            🔎
            <input type="text" id="buscarProveedor" placeholder="Buscar..." autocomplete="off">
          </div>

          <button type="button" class="btn primary" id="btnAgregarProveedor">➕ Agregar (F3)</button>
        </div>

        <div class="prov-table-wrap">
          <table class="module-table prov-table" id="tablaProveedores">
            <thead>
              <tr>
                <th style="width:90px;">No.</th>
                <th>Nombre / Representante</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($proveedores)): ?>
                <?php $i = 1; foreach ($proveedores as $p): ?>
                  <tr data-id="<?= $p['id'] ?>" data-text="<?= htmlspecialchars($p['nombre']) ?>">
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="2" class="prov-empty">No hay proveedores registrados</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="prov-actions">
          <button type="button" class="btn primary" id="btnSeleccionarProveedor">✅ Seleccionar</button>
        </div>

      </div>

    </div>
  </div>

  <!-- =========================
       MODAL AGREGAR PROVEEDOR
  ========================= -->
  <div id="modalAgregarProveedor" class="modal" style="display:none;">
    <div class="modal-window" style="max-width:520px;">

      <div class="modal-header">
        <div style="display:flex; align-items:center; gap:10px;">
          <span style="font-size:18px;">➕</span>
          <span>Datos de Proveedor</span>
        </div>
        <button type="button" class="prov-close" id="btnCerrarModalProveedor">✖</button>
      </div>

      <div class="modal-body">
        <form id="formAgregarProveedor">

          <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" id="mpNombre" autocomplete="off" required>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Teléfono</label>
              <input type="text" name="telefono" id="mpTelefono" autocomplete="off">
            </div>

            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" id="mpEmail" autocomplete="off">
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn primary" id="btnGuardarProveedor">💾 Guardar</button>
            <button type="button" class="btn secondary" id="btnCancelarProveedor">❌ Cancelar</button>
          </div>

        </form>
      </div>

    </div>
  </div>

  <!-- =========================
       MODAL CONFIRMAR ARTÍCULO
  ========================= -->
  <div id="modalConfirmarArticulo" class="modal" style="display:none;">
    <div class="modal-window" style="max-width:420px;">

      <div class="modal-header">
        Artículo no encontrado
        <button class="prov-close" style="margin-left:auto;" type="button" onclick="document.getElementById('modalConfirmarArticulo').style.display='none'">✖</button>
      </div>

      <div class="modal-body">
        <p>El artículo con código <strong id="codigoNoEncontrado"></strong> no existe.</p>
        <p>¿Deseas agregarlo?</p>
      </div>

      <div class="modal-footer">
        <button class="btn secondary" id="btnNoAgregar">❌ No</button>
        <button class="btn primary" id="btnSiAgregar">✔ Sí</button>
      </div>

    </div>
  </div>

  <script src="/public/assets/js/operaciones/compras.js"></script>
</div>
