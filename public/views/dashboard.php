<?php require __DIR__ . '/layout/header.php'; ?>

<div class="dash-page">

  <div class="dash-head">
    <div>
      <h2 class="dash-title">Dashboard</h2>
      <p class="dash-sub">Hola <?= htmlspecialchars($user['nombre']) ?> 👋</p>
    </div>
    <div class="dash-badges">
      <span class="dash-badge">Mi POS</span>
    </div>
  </div>

  <!-- =========================
       GRAFICA PRINCIPAL
  ========================== -->
  <section class="dash-card">
    <div class="dash-card-head">
      <div class="dash-card-title">
        <div class="dash-card-h">Ventas</div>
        <div class="dash-card-p" id="chartSubtitle">Cargando…</div>
      </div>

      <div class="dash-tabs" role="tablist" aria-label="Rango de ventas">
        <button class="dash-tab active" data-mode="week" type="button">Semana</button>
        <button class="dash-tab" data-mode="month" type="button">Mes</button>
        <button class="dash-tab" data-mode="year" type="button">12 meses</button>
      </div>
    </div>

    <div class="dash-chart-wrap">
      <canvas id="ventasChart" height="120"></canvas>
    </div>

    <div class="dash-footnote">
      Tip: usa los tabs para alternar el rango. No recarga la página 😎
    </div>
  </section>

  <!-- =========================
       ALERTAS INTELIGENTES
  ========================== -->
  <section class="dash-alerts">
    <div class="dash-alert-card">
      <div class="dash-alert-head">
        <div class="dash-alert-icon">📦</div>
        <div>
          <div class="dash-alert-title">Productos por agotarse</div>
          <div class="dash-alert-sub">Top críticos (stock &lt; mínimo)</div>
        </div>
      </div>
      <div class="dash-alert-body" id="alertStock">
        <div class="dash-empty">Cargando…</div>
      </div>
    </div>

    <div class="dash-alert-card">
      <div class="dash-alert-head">
        <div class="dash-alert-icon">💳</div>
        <div>
          <div class="dash-alert-title">Cuentas por cobrar vencidas</div>
          <div class="dash-alert-sub">Solo vencidas (las que duelen)</div>
        </div>
      </div>
      <div class="dash-alert-body" id="alertCxc">
        <div class="dash-empty">Cargando…</div>
      </div>
    </div>

    <div class="dash-alert-card">
      <div class="dash-alert-head">
        <div class="dash-alert-icon">🧾</div>
        <div>
          <div class="dash-alert-title">Corte pendiente</div>
          <div class="dash-alert-sub">Del día anterior</div>
        </div>
      </div>
      <div class="dash-alert-body" id="alertCorte">
        <div class="dash-empty">Cargando…</div>
      </div>
    </div>
  </section>

</div>

<!-- Chart.js (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<!-- Tu JS -->
<script src="/assets/js/components/dashboard.js"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>