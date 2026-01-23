<div class="cc-page">

  <div class="cc-header">
    <h2 class="module-title">Compras a Crédito</h2>

    <div class="cc-actions">
      <button type="button" class="btn primary" id="btnRefrescar">🔄 Refrescar (F5)</button>
      <button type="button" class="btn" id="btnVerDetalle">🔎 Ver detalle (Enter)</button>
      <button type="button" class="btn success" id="btnAbonar">💳 Abonar (F3)</button>
      <button type="button" class="btn danger" id="btnMarcarPagada">✅ Marcar pagada (DEL)</button>
    </div>
  </div>

  <div class="cc-shell">

    <div class="cc-filters">
      <div class="field">
        <label>Proveedor</label>
        <select id="fProveedor">
          <option value="">Todos</option>
          <?php foreach ($proveedores as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field">
        <label>Estatus</label>
        <select id="fEstatus">
          <option value="">Todos</option>
          <option value="PENDIENTE">PENDIENTE</option>
          <option value="PAGADA">PAGADA</option>
        </select>
      </div>

      <div class="field">
        <label>Desde</label>
        <input type="date" id="fDesde">
      </div>

      <div class="field">
        <label>Hasta</label>
        <input type="date" id="fHasta">
      </div>

    </div>

    <div class="cc-divider"></div>

    <div class="cc-topline">
      <div class="cc-pill">
        <span>Docs</span>
        <strong id="rDocs">0</strong>
      </div>
      <div class="cc-pill">
        <span>Total</span>
        <strong>$<span id="rTotal">0.00</span></strong>
      </div>
      <div class="cc-pill">
        <span>Saldo</span>
        <strong>$<span id="rSaldo">0.00</span></strong>
      </div>
      <div class="cc-pill big">
        <span>Pendiente</span>
        <strong>$<span id="rPendiente">0.00</span></strong>
      </div>
    </div>

    <div class="cc-tablewrap">
      <table class="module-table cc-table">
        <thead>
          <tr>
            <th>Proveedor</th>
            <th>Folio</th>
            <th>Fecha</th>
            <th>Vence</th>
            <th style="text-align:right;">Total</th>
            <th style="text-align:right;">Saldo</th>
            <th>Estatus</th>
          </tr>
        </thead>
        <tbody id="ccBody"></tbody>
      </table>
    </div>

  </div>

  <!-- =========================
       MODAL DETALLE
  ========================= -->
  <div id="modalDetalleCxp" class="modal" style="display:none;">
    <div class="modal-window" style="max-width:980px; width:980px;">
      <div class="modal-header">
        <span>Detalle de Cuenta por Pagar</span>
        <button type="button" class="prov-close" id="btnCerrarDetalle">✖</button>
      </div>

      <div class="modal-body">
        <div class="cc-det-grid">
          <div class="cc-card">
            <div class="cc-card-title">Cuenta</div>
            <div class="cc-kv"><span>Proveedor</span><strong id="dProveedor">-</strong></div>
            <div class="cc-kv"><span>Folio</span><strong id="dFolio">-</strong></div>
            <div class="cc-kv"><span>Concepto</span><strong id="dConcepto">-</strong></div>
            <div class="cc-kv"><span>Fecha</span><strong id="dFecha">-</strong></div>
            <div class="cc-kv"><span>Vence</span><strong id="dVence">-</strong></div>
            <div class="cc-kv"><span>Total</span><strong id="dTotal">-</strong></div>
            <div class="cc-kv"><span>Saldo</span><strong id="dSaldo">-</strong></div>
            <div class="cc-kv"><span>Estatus</span><strong id="dEstatus">-</strong></div>
          </div>

          <div class="cc-card">
            <div class="cc-card-title">Artículos (de la compra)</div>
            <div class="cc-mini-tablewrap">
              <table class="module-table cc-mini-table">
                <thead>
                  <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th style="text-align:right;">Cant</th>
                    <th style="text-align:right;">Costo</th>
                    <th style="text-align:right;">Importe</th>
                  </tr>
                </thead>
                <tbody id="dItems"></tbody>
              </table>
            </div>
            <div class="cc-muted" id="dNoCompra" style="display:none;">Esta cuenta no está ligada a una compra (compra_id vacío).</div>
          </div>

          <div class="cc-card">
            <div class="cc-card-title">Pagos</div>
            <div class="cc-mini-tablewrap">
              <table class="module-table cc-mini-table">
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th style="text-align:right;">Monto</th>
                  </tr>
                </thead>
                <tbody id="dPagos"></tbody>
              </table>
            </div>
            <div class="cc-muted" id="dNoPagos" style="display:none;">
              No hay pagos registrados (o falta la tabla <code>cuentas_pagar_pagos</code>).
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn secondary" id="btnCerrarDetalle2">Cerrar</button>
      </div>
    </div>
  </div>

  <!-- =========================
       MODAL ABONO
  ========================= -->
  <div id="modalAbonoCxp" class="modal" style="display:none;">
    <div class="modal-window" style="max-width:560px; width:560px;">
      <div class="modal-header">
        <span>Registrar abono</span>
        <button type="button" class="prov-close" id="btnCerrarAbono">✖</button>
      </div>

      <div class="modal-body">
        <div class="cc-abono-head">
          <div><strong id="aProveedor">-</strong></div>
          <div class="cc-muted">Saldo actual: <strong>$<span id="aSaldo">0.00</span></strong></div>
        </div>

        <div class="cc-form">
          <div class="field">
            <label>Fecha</label>
            <input type="date" id="aFecha">
          </div>

          <div class="field">
            <label>Monto</label>
            <input type="number" id="aMonto" step="0.01" min="0.01" placeholder="0.00">
          </div>

          <div class="field">
            <label>Método</label>
            <select id="aMetodo">
              <option value="EFECTIVO">EFECTIVO</option>
              <option value="TARJETA">TARJETA</option>
              <option value="TRANSFERENCIA">TRANSFERENCIA</option>
              <option value="OTRO">OTRO</option>
            </select>
          </div>

          <div class="field">
            <label>Referencia</label>
            <input type="text" id="aReferencia" placeholder="Opcional...">
          </div>

          <div class="field full">
            <label>Notas</label>
            <input type="text" id="aNotas" placeholder="Opcional...">
          </div>

          <div class="field check full">
            <label>Afecta caja</label>
            <div class="check-row">
              <input type="checkbox" id="aAfectaCaja">
              <span>Actívalo solo si salió dinero de caja</span>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn secondary" id="btnCancelarAbono">Cancelar</button>
        <button type="button" class="btn success" id="btnGuardarAbono">💾 Guardar</button>
      </div>
    </div>
  </div>

  <script src="/public/assets/js/consultas/compras_credito.js"></script>
</div>
