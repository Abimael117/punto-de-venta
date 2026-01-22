<div class="consultas-page">

  <h2 class="module-title">Consultas</h2>

  <div class="consultas-grid">

    <!-- Ventas -->
    <div class="consulta-card" onclick="location.href='/consultas/ventas'" role="button" tabindex="0">
      <div class="card-ico">🧾</div>
      <div class="card-txt">
        <h3>Ventas</h3>
        <p>Historial de ventas realizadas</p>
      </div>
      <div class="card-go">›</div>
    </div>

    <!-- Ventas a crédito -->
    <div class="consulta-card" onclick="location.href='/consultas/ventas-credito'" role="button" tabindex="0">
      <div class="card-ico">💳</div>
      <div class="card-txt">
        <h3>Ventas a Crédito</h3>
        <p>Ventas fiadas, saldos y vencimientos</p>
      </div>
      <div class="card-go">›</div>
    </div>

    <!-- Detalle de ventas -->
    <div class="consulta-card" onclick="location.href='/consultas/ventas-detalle'" role="button" tabindex="0">
      <div class="card-ico">📦</div>
      <div class="card-txt">
        <h3>Detalle de Ventas</h3>
        <p>Artículos vendidos por venta</p>
      </div>
      <div class="card-go">›</div>
    </div>

    <!-- Compras -->
    <div class="consulta-card" onclick="location.href='/consultas/compras'" role="button" tabindex="0">
      <div class="card-ico">📥</div>
      <div class="card-txt">
        <h3>Compras</h3>
        <p>Historial de compras a proveedores</p>
      </div>
      <div class="card-go">›</div>
    </div>

    <!-- Compras a crédito -->
    <div class="consulta-card" onclick="location.href='/consultas/compras-credito'" role="button" tabindex="0">
      <div class="card-ico">🏦</div>
      <div class="card-txt">
        <h3>Compras a Crédito</h3>
        <p>Cuentas por pagar y vencimientos</p>
      </div>
      <div class="card-go">›</div>
    </div>

    <!-- Movimientos de inventario -->
    <div class="consulta-card" onclick="location.href='/consultas/inventario'" role="button" tabindex="0">
      <div class="card-ico">📊</div>
      <div class="card-txt">
        <h3>Inventario</h3>
        <p>Entradas y salidas de artículos</p>
      </div>
      <div class="card-go">›</div>
    </div>

  </div>

</div>

<script>
  // Enter para abrir cards (teclado)
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    const el = document.activeElement;
    if (el && el.classList && el.classList.contains('consulta-card')) el.click();
  });
</script>
