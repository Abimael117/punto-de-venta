<!-- MODAL COBRO -->
<div id="modalCobro" class="modal-cobro hidden">
  <div class="modal-window cobro-window">

    <div class="modal-header cobro-header">
      <div class="cobro-title">Ticket</div>
      <button class="cobro-close" id="cerrarCobro" type="button">✕</button>
    </div>

    <div class="modal-body cobro-body">

      <div class="cobro-total">
        <div class="cobro-total-label">Total a pagar</div>
        <div class="cobro-total-value">$ <span id="totalCobro">0.00</span></div>
      </div>

      <!-- Métodos -->
      <div class="cobro-metodos">

        <div class="cobro-row">
          <div class="cobro-metodo">
            <span class="ico">💵</span> Efectivo
          </div>
          <input type="number" step="0.01" id="pagoEfectivo" value="0">
        </div>

        <div class="cobro-row">
          <div class="cobro-metodo">
            <span class="ico">💳</span> Tarjeta
          </div>
          <input type="number" step="0.01" id="pagoTarjeta" value="0">
        </div>

        <div class="cobro-row">
          <div class="cobro-metodo">
            <span class="ico">🏦</span> Transferencia
          </div>
          <input type="number" step="0.01" id="pagoTransferencia" value="0">
        </div>

        <div class="cobro-row ref-row">
          <div class="cobro-metodo">
            <span class="ico">🧾</span> Referencia
          </div>
          <input type="text" id="pagoReferencia" placeholder="Opcional">
        </div>

      </div>

      <div class="cobro-cambio">
        <div class="cobro-cambio-label">Cambio</div>
        <div class="cobro-cambio-value">$ <span id="cambioCobro">0.00</span></div>
      </div>

      <!-- Botón tipo SICAR -->
      <div class="cobro-credito-toggle">
        <button type="button" class="btn credit" id="btnToggleCredito">
          🧾 Crédito
        </button>
        <div class="mini-note" id="creditoHint">
          (fiado / a crédito)
        </div>
      </div>

      <!-- Panel Crédito (colapsable) -->
      <div id="creditoPanel" class="credito-panel hidden">

        <div class="credito-grid">
          <div class="cfield">
            <label>Días de Crédito</label>
            <input type="number" id="creditoDias" value="0" min="0">
          </div>

          <div class="cfield">
            <label>Límite de Crédito</label>
            <input type="text" id="creditoLimite" value="0.00" readonly>
          </div>

          <div class="cfield">
            <label>Adeudo Actual</label>
            <input type="text" id="creditoAdeudo" value="0.00" readonly>
          </div>

          <div class="cfield">
            <label>Crédito Disponible</label>
            <input type="text" id="creditoDisponible" value="0.00" readonly>
          </div>

          <div class="cfield">
            <label>Fecha de Vencimiento</label>
            <input type="text" id="creditoVence" value="-" readonly>
          </div>

          <div class="cfield">
            <label>Total a Crédito</label>
            <input type="text" id="creditoTotal" value="0.00" readonly>
          </div>
        </div>

        <div class="credito-warning" id="creditoWarning" style="display:none;"></div>

      </div>

    </div>

    <div class="modal-footer cobro-footer">
      <button class="btn primary" id="confirmarCobro" type="button">Aceptar</button>
    </div>

  </div>
</div>
