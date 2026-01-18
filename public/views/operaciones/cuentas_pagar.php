<div class="cp-page">

  <h2 class="module-title">Cuentas por Pagar</h2>

  <div class="cp-toolbar">
    <button class="btn primary" id="btnRecargar">↻ Recargar</button>
    <button class="btn secondary" id="btnEstado">✅ PENDIENTES</button>
    <button class="btn danger" id="btnSalir">⬅ Volver</button>
  </div>

  <div class="cp-card">

    <div class="cp-topbar">
      <div class="cp-search">
        <span>🔎</span>
        <input id="cpBuscar" placeholder="Buscar por proveedor, id, estado, montos...">
      </div>

      <div class="cp-kpis">
        <div class="cp-kpi">
          <div class="cp-kpi-label">Total</div>
          <div class="cp-kpi-val" id="kpiTotal">$0.00</div>
        </div>
        <div class="cp-kpi">
          <div class="cp-kpi-label">Pagado</div>
          <div class="cp-kpi-val" id="kpiPagado">$0.00</div>
        </div>
        <div class="cp-kpi">
          <div class="cp-kpi-label">Saldo</div>
          <div class="cp-kpi-val" id="kpiSaldo">$0.00</div>
        </div>
      </div>
    </div>

    <table class="module-table" id="tablaCP">
      <thead>
        <tr>
          <th>ID</th>
          <th>Compra</th>
          <th>Proveedor</th>
          <th>Vence</th>
          <th>Estatus</th>
          <th style="text-align:right;">Total</th>
          <th style="text-align:right;">Pagado</th>
          <th style="text-align:right;">Saldo</th>
          <th style="width:150px;">Acción</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>

  </div>
</div>

<!-- MODAL: DETALLE / PAGOS -->
<div class="modal" id="modalCP" style="display:none;">
  <div class="modal-window" style="width:980px; max-width:96%;">
    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:18px;">💸</span>
        <span>Detalle de deuda</span>
      </div>
      <button class="prov-close" id="btnCerrarCP" type="button">✕</button>
    </div>

    <div class="modal-body">

      <div class="cp-detail">
        <div class="cp-pill"><span>ID</span><strong id="dId">—</strong></div>
        <div class="cp-pill"><span>Proveedor</span><strong id="dProveedor">—</strong></div>
        <div class="cp-pill"><span>Total</span><strong id="dTotal">$0.00</strong></div>
        <div class="cp-pill"><span>Pagado</span><strong id="dPagado">$0.00</strong></div>
        <div class="cp-pill"><span>Saldo</span><strong id="dSaldo">$0.00</strong></div>
      </div>

      <div class="cp-paybox">
        <div class="field">
          <label>Método</label>
          <select id="pMetodo" class="cp-select">
            <option value="EFECTIVO">EFECTIVO</option>
            <option value="TARJETA">TARJETA</option>
            <option value="TRANSFERENCIA">TRANSFERENCIA</option>
            <option value="CHEQUE">CHEQUE</option>
            <option value="OTRO">OTRO</option>
          </select>
        </div>

        <div class="field">
          <label>Monto</label>
          <input type="number" step="0.01" id="pMonto" placeholder="0.00">
        </div>

        <div class="field">
          <label>Referencia</label>
          <input type="text" id="pRef" placeholder="Opcional: folio, nota...">
        </div>

        <div class="cp-actions">
          <button class="btn primary" id="btnPagar">💾 Registrar pago</button>
        </div>
      </div>

      <h3 class="cp-subtitle">Pagos</h3>

      <table class="module-table" id="tablaPagos">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Método</th>
            <th style="text-align:right;">Monto</th>
            <th>Referencia</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>

    </div>

    <div class="modal-footer">
      <button class="btn secondary" id="btnCerrarCP2" type="button">Cerrar</button>
    </div>
  </div>
</div>

<script src="/public/assets/js/operaciones/cuentas_pagar.js"></script>
