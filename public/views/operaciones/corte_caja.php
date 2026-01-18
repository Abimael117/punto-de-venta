<div class="corte-page">

  <h2 class="module-title">Corte de Caja</h2>

  <div class="corte-toolbar">
    <button class="btn primary" id="btnGuardar">💾 Guardar (F3)</button>
    <button class="btn secondary" id="btnHistorial">📚 Historial (F9)</button>
    <button class="btn danger" id="btnCancelar">❌ Cancelar (ESC)</button>
  </div>

  <div class="corte-layout">

    <div class="table-card">
      <div class="corte-top">

        <div class="field">
          <label>Fecha del corte</label>
          <input type="date" id="fechaCorte" value="<?= date('Y-m-d') ?>">
        </div>

        <div class="field">
          <label>Hora del corte</label>
          <input type="time" id="horaCorte" value="<?= date('H:i') ?>">
        </div>

        <div class="hint">
          Tip: Si estás en caja, esto se hace diario. Si no cuadra… el dinero “se fue a pasear” sin avisar 😄
        </div>

      </div>

      <div class="resumen-grid">
        <div class="kpi">
          <div class="kpi-label">Total sistema</div>
          <div class="kpi-value" id="kpiTotal">$0.00</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">Efectivo sistema</div>
          <div class="kpi-value" id="kpiEfectivo">$0.00</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">Tarjeta sistema</div>
          <div class="kpi-value" id="kpiTarjeta">$0.00</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">Transferencia sistema</div>
          <div class="kpi-value" id="kpiTransferencia">$0.00</div>
        </div>
        <div class="kpi">
          <div class="kpi-label">Crédito sistema</div>
          <div class="kpi-value" id="kpiCredito">$0.00</div>
        </div>
      </div>

      <div class="corte-captura">

        <div class="field">
          <label>Dinero inicial (apertura)</label>
          <input type="number" step="0.01" id="ccDineroInicial" placeholder="0.00">
          <small class="cc-help">¿Con cuánto se abrió la caja?</small>
        </div>

        <div class="field">
          <label>Compras pagadas con caja</label>
          <input type="number" step="0.01" id="ccComprasCaja" placeholder="0.00">
          <small class="cc-help">Solo efectivo que salió de la caja.</small>
        </div>

        <div class="pill cc-pill-calc">
          <span>Caja calculada</span>
          <strong id="ccCajaCalc">$0.00</strong>
          <em class="cc-sub">= inicial + efectivo sistema − compras caja</em>
        </div>

        <div class="field">
          <label>Efectivo contado</label>
          <input type="number" step="0.01" id="efectivoContado" placeholder="0.00">
        </div>

        <div class="pill">
          <span>Diferencia (contado vs calculado)</span>
          <strong id="difMonto">$0.00</strong>
        </div>

        <div class="field full">
          <label>Notas</label>
          <input type="text" id="notasCorte" placeholder="Opcional: observaciones del corte">
        </div>

      </div>

      <div class="totales">
        <div class="total-pill">
          <span>Efectivo sistema</span>
          <span id="esperadoEfectivo">0.00</span>
        </div>
      </div>

    </div>

  </div>
</div>

<!-- MODAL: HISTORIAL -->
<div class="modal" id="modalHistorial" style="display:none;">
  <div class="modal-window" style="width:860px; max-width:96%;">
    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:18px;">📚</span>
        <span>Historial de cortes</span>
      </div>
      <button class="prov-close" id="btnCerrarHistorial" type="button">✕</button>
    </div>

    <div class="modal-body">
      <div class="prov-topbar">
        <div class="prov-search" style="flex:1;">
          <span>🔎</span>
          <input id="buscarHistorial" placeholder="Buscar por fecha, hora, notas o montos...">
        </div>
        <button class="btn secondary" id="btnRecargarHistorial">↻ Recargar</button>
      </div>

      <table class="module-table" id="tablaHistorial">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Total</th>
            <th>Efectivo</th>
            <th>Contado</th>
            <th>Diferencia</th>
            <th>Notas</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>

    <div class="modal-footer">
      <button class="btn secondary" id="btnCerrarHistorial2" type="button">Cerrar</button>
    </div>
  </div>
</div>

<script src="/public/assets/js/operaciones/corte_caja.js"></script>
