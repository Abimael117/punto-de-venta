<div class="vd-page">

  <!-- Header / Title (igual que Compras) -->
  <div class="vd-header">
    <h2 class="module-title">Detalle de Ventas</h2>
    <div class="vd-sub">Artículos vendidos por venta (evidencia nivel “no me acuerdo jefe”) 😌</div>
  </div>

  <!-- =========================
       SUPER CARD (TODO JUNTO)
  ========================= -->
  <div class="vd-shell">

    <!-- FILTROS -->
    <div class="vd-section">
      <div class="section-head">
        <div class="section-title">
          <span class="section-dot"></span>
          <span>Filtros</span>
        </div>
        <div class="section-sub">Filtra por fechas, tipo (crédito/contado) o texto</div>
      </div>

      <div class="vd-toolbar">

        <div class="field">
          <label>Desde</label>
          <input type="date" id="vdDesde">
        </div>

        <div class="field">
          <label>Hasta</label>
          <input type="date" id="vdHasta">
        </div>

        <div class="field">
          <label>Crédito</label>
          <select id="vdCredito">
            <option value="all">Todas</option>
            <option value="1">Solo crédito</option>
            <option value="0">Solo contado</option>
          </select>
        </div>

        <div class="field grow">
          <label>Buscar</label>
          <input type="text" id="vdQ" placeholder="Folio, cliente, código cliente, artículo...">
        </div>

        <div class="vd-actions">
          <button type="button" class="btn primary" id="vdBtnBuscar">🔎 Buscar</button>
          <button type="button" class="btn secondary" id="vdBtnLimpiar">🧹 Limpiar</button>
        </div>

      </div>
    </div>

    <div class="section-divider"></div>

    <!-- TABLA -->
    <div class="vd-section">
      <div class="section-head section-head-row">
        <div>
          <div class="section-title">
            <span class="section-dot"></span>
            <span>Resultados</span>
          </div>
          <div class="section-sub">Click en una venta para ver su detalle</div>
        </div>
      </div>

      <div class="table-wrap">
        <table class="module-table vd-table" id="vdTabla">
          <thead>
            <tr>
              <th style="width:170px;">Fecha</th>
              <th style="width:150px;">Folio</th>
              <th>Cliente</th>
              <th style="width:110px;">Tipo</th>
              <th>Artículo</th>
              <th class="num" style="width:90px;">Cant.</th>
              <th class="num" style="width:110px;">Precio</th>
              <th class="num" style="width:120px;">Importe</th>
              <th class="num" style="width:120px;">Total</th>
            </tr>
          </thead>
          <tbody id="vdTbody">
            <tr><td class="vd-empty" colspan="9">Cargando...</td></tr>
          </tbody>
        </table>
      </div>

    </div>

  </div><!-- /vd-shell -->

</div>

<!-- =========================
     MODAL DETALLE
========================= -->
<div class="modal" id="vdModal" style="display:none;">
  <div class="modal-window vd-modal-window">

    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:18px;">🧾</span>
        <span>Detalle de venta</span>
      </div>
      <button type="button" class="prov-close" id="vdModalCerrar">✖</button>
    </div>

    <div class="modal-body">
      <div class="vd-modal-meta" id="vdModalMeta"></div>

      <div class="vd-modal-card">
        <div class="table-wrap">
          <table class="module-table vd-table vd-table-mini">
            <thead>
              <tr>
                <th style="width:130px;">Código</th>
                <th>Artículo</th>
                <th class="num" style="width:90px;">Cant.</th>
                <th class="num" style="width:110px;">Precio</th>
                <th class="num" style="width:120px;">Importe</th>
              </tr>
            </thead>
            <tbody id="vdModalTbody">
              <tr><td class="vd-empty" colspan="5">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="button" class="btn secondary" id="vdModalCerrar2">Cerrar</button>
    </div>

  </div>
</div>

<script src="/public/assets/js/consultas/ventas_detalle.js"></script>
