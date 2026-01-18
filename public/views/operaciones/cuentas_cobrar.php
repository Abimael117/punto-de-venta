<div class="cc-page">

  <h2 class="module-title">Cuentas por Cobrar</h2>

  <div class="cc-toolbar">
    <button class="btn primary" id="btnRecargar">↻ Recargar</button>
    <button class="btn secondary" id="btnEstado">✅ PENDIENTES</button>
    <button class="btn danger" id="btnSalir">⬅ Volver</button>
  </div>

  <div class="cc-card">

    <div class="cc-topbar">
      <div class="cc-search">
        <span>🔎</span>
        <input id="ccBuscar" placeholder="Buscar por cliente, id, estado, montos...">
      </div>

      <div class="cc-kpis">
        <div class="cc-kpi">
          <div class="cc-kpi-label">Total</div>
          <div class="cc-kpi-val" id="kpiTotal">$0.00</div>
        </div>
        <div class="cc-kpi">
          <div class="cc-kpi-label">Pagado</div>
          <div class="cc-kpi-val" id="kpiPagado">$0.00</div>
        </div>
        <div class="cc-kpi">
          <div class="cc-kpi-label">Saldo</div>
          <div class="cc-kpi-val" id="kpiSaldo">$0.00</div>
        </div>
      </div>
    </div>

    <table class="module-table" id="tablaCC">
      <thead>
        <tr>
          <th>ID</th>
          <th>Venta</th>
          <th>Cliente</th>
          <th>Vence</th>
          <th>Estado</th>
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
<div class="modal" id="modalCC" style="display:none;">
  <div class="modal-window" style="width:980px; max-width:96%;">
    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:18px;">💳</span>
        <span>Detalle de crédito</span>
      </div>
      <button class="prov-close" id="btnCerrarCC" type="button">✕</button>
    </div>

    <div class="modal-body">

      <div class="cc-detail">
        <div class="cc-pill"><span>ID</span><strong id="dId">—</strong></div>
        <div class="cc-pill"><span>Cliente</span><strong id="dCliente">—</strong></div>
        <div class="cc-pill"><span>Total</span><strong id="dTotal">$0.00</strong></div>
        <div class="cc-pill"><span>Pagado</span><strong id="dPagado">$0.00</strong></div>
        <div class="cc-pill"><span>Saldo</span><strong id="dSaldo">$0.00</strong></div>
      </div>

      <div class="cc-paybox">
        <div class="field">
          <label>Método</label>
          <select id="pMetodo" class="cc-select">
            <option value="EFECTIVO">EFECTIVO</option>
            <option value="TARJETA">TARJETA</option>
            <option value="TRANSFERENCIA">TRANSFERENCIA</option>
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

        <div class="cc-actions">
          <button class="btn primary" id="btnPagar">💾 Registrar pago</button>
        </div>
      </div>

      <h3 class="cc-subtitle">Pagos</h3>

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
      <button class="btn secondary" id="btnCerrarCC2" type="button">Cerrar</button>
    </div>
  </div>
</div>

<script src="/public/assets/js/operaciones/cuentas_cobrar.js"></script>
