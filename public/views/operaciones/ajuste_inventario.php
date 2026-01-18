<div class="invadj-page">

  <h2 class="module-title">Ajuste de Inventario</h2>

  <div class="invadj-toolbar">
    <button class="btn primary" id="btnGuardar">💾 Guardar (F3)</button>
    <button class="btn danger" id="btnCancelar">❌ Cancelar (ESC)</button>
  </div>

  <div class="invadj-card">

    <div class="invadj-top">
      <div class="invadj-search">
        <span>🔎</span>
        <input id="codigoArticulo" placeholder="Buscar por código o descripción..." autocomplete="off">
      </div>

      <div class="invadj-meta">
        <div class="field">
          <label>Fecha</label>
          <input type="datetime-local" id="ajFecha">
        </div>
        <div class="field">
          <label>Notas</label>
          <input type="text" id="ajNotas" placeholder="Opcional...">
        </div>
      </div>
    </div>

    <!-- ÚNICA TABLA -->
    <table class="module-table" id="tablaAjuste">
      <thead>
        <tr>
          <th>Código</th>
          <th>Descripción</th>
          <th style="text-align:right;">Existencia actual</th>
          <th style="text-align:right;">Stock físico</th>
          <th style="text-align:right;">Ajuste</th>
          <th style="text-align:right;">Importe</th>
          <th style="text-align:right; width:190px;">Acción</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

    <div class="invadj-kpis">
      <div class="invadj-kpi"><span>Artículos ajustados</span><strong id="kpiItems">0</strong></div>
      <div class="invadj-kpi"><span>Neto</span><strong id="kpiNeto">$0.00</strong></div>
    </div>

  </div>
</div>

<!-- MODAL: CAPTURAR STOCK FÍSICO -->
<div class="modal" id="modalAjuste" style="display:none;">
  <div class="modal-window" style="width:780px; max-width:96%;">
    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:18px;">🧮</span>
        <span>Capturar ajuste</span>
      </div>
      <button class="prov-close" id="btnCerrarAjuste" type="button">✕</button>
    </div>

    <div class="modal-body">
      <div class="invadj-detail">
        <div class="cc-pill"><span>Código</span><strong id="mCodigo">—</strong></div>
        <div class="cc-pill"><span>Descripción</span><strong id="mDesc">—</strong></div>
        <div class="cc-pill"><span>Existencia actual</span><strong id="mStock">0.00</strong></div>
      </div>

      <div class="invadj-paybox">
        <div class="field">
          <label>Stock físico</label>
          <input type="number" step="0.01" id="mFisico" placeholder="0.00">
        </div>

        <div class="cc-actions">
          <button class="btn primary" id="btnAplicarAjuste">✅ Aplicar</button>
        </div>
      </div>

      <div class="invadj-hint" id="mHint">Ajuste: —</div>
    </div>

    <div class="modal-footer">
      <button class="btn secondary" id="btnCerrarAjuste2" type="button">Cerrar</button>
    </div>
  </div>
</div>

<script src="/public/assets/js/operaciones/ajuste_inventario.js"></script>
