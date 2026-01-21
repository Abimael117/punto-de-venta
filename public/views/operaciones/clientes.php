<div class="module-container clientes-page">

  <div class="clientes-toolbar">
    <button class="btn primary" id="btnAgregar">➕ Agregar (F3)</button>
    <button class="btn secondary" id="btnEditar">✏️ Editar (F4)</button>
    <button class="btn danger" id="btnEliminar">🗑 Eliminar (F6)</button>
  </div>

  <div class="table-card">
    <table class="module-table" id="tablaClientes">
      <thead>
        <tr>
          <th>Código</th>
          <th>Nombre</th>
          <th>Teléfono</th>
          <th>Crédito</th>
          <th style="text-align:right;">Límite</th>
        </tr>
      </thead>
      <tbody id="clientesBody">
        <tr><td colspan="5" class="prov-empty">Cargando...</td></tr>
      </tbody>
    </table>
  </div>

  <!-- =========================
       MODAL: CLIENTE
  ========================= -->
  <div class="modal" id="modalCliente" style="display:none;">
    <div class="modal-window" style="width:720px; max-width:95%;">
      <div class="modal-header">
        <span id="mcTitulo">Cliente</span>
        <button class="prov-close" id="mcCerrar" type="button">✕</button>
      </div>

      <div class="modal-body">
        <form id="formCliente">
          <input type="hidden" name="id" id="cli_id">

          <div class="form-row">
            <div class="form-group">
              <label>Nombre *</label>
              <input type="text" name="nombre" id="cli_nombre" required autocomplete="off">
            </div>

            <div class="form-group">
              <label>Teléfono</label>
              <input type="text" name="telefono" id="cli_telefono" autocomplete="off">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" id="cli_email" autocomplete="off">
            </div>

            <div class="form-group">
              <label>Dirección</label>
              <input type="text" name="direccion" id="cli_direccion" autocomplete="off">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Permite crédito</label>
              <select name="permite_credito" id="cli_perm_credito">
                <option value="0">No</option>
                <option value="1">Sí</option>
              </select>
            </div>

            <div class="form-group">
              <label>Límite crédito</label>
              <input type="number" step="0.01" name="limite_credito" id="cli_limite" value="0">
            </div>

            <div class="form-group">
              <label>Días crédito</label>
              <input type="number" name="dias_credito" id="cli_dias" value="0">
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn secondary" id="mcCancelar">❌ Cancelar</button>
            <button type="submit" class="btn primary" id="mcGuardar">💾 Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="/public/assets/js/operaciones/clientes.js"></script>
</div>
