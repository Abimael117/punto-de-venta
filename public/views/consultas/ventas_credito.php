<div class="vc-page">

  <!-- Header / Title -->
  <div class="vc-header">
    <h2 class="module-title">Ventas a Crédito</h2>
    <div class="vc-sub">Saldos, vencimientos y evidencia (para el “yo no fui”) 😌</div>

    <div class="vc-actions">
      <button type="button" class="btn primary" id="vcBtnBuscar">🔎 Buscar</button>
      <button type="button" class="btn secondary" id="vcBtnLimpiar">🧹 Limpiar</button>
    </div>
  </div>

  <!-- SUPER CARD -->
  <div class="vc-shell">

    <!-- FILTROS -->
    <div class="vc-section">
      <div class="section-head">
        <div class="section-title">
          <span class="section-dot"></span>
          <span>Filtros</span>
        </div>
        <div class="section-sub">Filtra por fechas, estado y texto</div>
      </div>

      <div class="vc-filters">
        <div class="field">
          <label>Desde</label>
          <input type="date" id="vcDesde">
        </div>

        <div class="field">
          <label>Hasta</label>
          <input type="date" id="vcHasta">
        </div>

        <div class="field">
          <label>Estado</label>
          <select id="vcEstado">
            <option value="all">Todos</option>
            <option value="vencidos">Vencidos</option>
            <option value="porvencer">Por vencer (≤ 3 días)</option>
            <option value="aldia">Al día</option>
          </select>
        </div>

        <div class="field grow">
          <label>Buscar</label>
          <input type="text" id="vcQ" placeholder="Cliente o código cliente...">
        </div>
      </div>
    </div>

    <div class="section-divider"></div>

    <!-- TABLA -->
    <div class="vc-section">
      <div class="section-head section-head-row">
        <div>
          <div class="section-title">
            <span class="section-dot"></span>
            <span>Totales por Cliente</span>
          </div>
          <div class="section-sub">Click en un cliente para ver su desglose</div>
        </div>

        <div class="vc-minihelp">
          <span class="pill ok">AL DÍA</span>
          <span class="pill warn">POR VENCER</span>
          <span class="pill bad">VENCIDO</span>
        </div>
      </div>

      <div class="table-wrap">
        <table class="module-table vc-table" id="vcTabla">
          <thead>
            <tr>
              <th>Cliente</th>
              <th class="num" style="width:140px;">Total</th>
              <th class="num" style="width:140px;">Saldo</th>
              <th class="num" style="width:110px;">Créditos</th>
              <th style="width:140px;">Próx. vence</th>
              <th class="num" style="width:90px;">Días</th>
              <th style="width:130px;">Estado</th>
            </tr>
          </thead>
          <tbody id="vcTbody">
            <tr><td colspan="7" class="vc-empty">Cargando...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div><!-- /vc-shell -->

</div>

<!-- MODAL DETALLE CLIENTE -->
<div class="modal" id="vcModal" style="display:none;">
  <div class="modal-window vc-modal">

    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:18px;">💳</span>
        <span>Créditos del Cliente</span>
      </div>
      <button type="button" class="prov-close" id="vcModalCerrar">✖</button>
    </div>

    <div class="modal-body">

      <div class="vc-modal-meta" id="vcModalMeta"></div>

      <div class="vc-modal-actions">
        <button type="button" class="btn primary" id="vcBtnVerArticulos" disabled>📦 Ver artículos (evidencia)</button>
      </div>

      <div class="vc-modal-block">
        <div class="block-title">Desglose</div>

        <div class="table-wrap">
          <table class="module-table vc-table vc-table-mini">
            <thead>
              <tr>
                <th style="width:170px;">Fecha</th>
                <th style="width:190px;">Folio</th>
                <th class="num" style="width:120px;">Total</th>
                <th class="num" style="width:120px;">Saldo</th>
                <th style="width:140px;">Vence</th>
                <th class="num" style="width:90px;">Días</th>
                <th style="width:130px;">Estado</th>
              </tr>
            </thead>
            <tbody id="vcCreditosTbody">
              <tr><td colspan="7" class="vc-empty">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
        <div class="vc-hint">Tip: click en un crédito para ver abonos y habilitar “Ver artículos”.</div>
      </div>

      <div class="vc-modal-block">
        <div class="block-title">Abonos</div>

        <div class="table-wrap">
          <table class="module-table vc-table vc-table-mini">
            <thead>
              <tr>
                <th style="width:170px;">Fecha</th>
                <th class="num" style="width:140px;">Monto</th>
                <th style="width:150px;">Método</th>
                <th>Referencia</th>
              </tr>
            </thead>
            <tbody id="vcPagosTbody">
              <tr><td colspan="4" class="vc-empty">Selecciona un crédito…</td></tr>
            </tbody>
          </table>
        </div>

      </div>

    </div>

    <div class="modal-footer">
      <button type="button" class="btn secondary" id="vcModalCerrar2">Cerrar</button>
    </div>

  </div>
</div>

<script src="/public/assets/js/consultas/ventas_credito.js"></script>
