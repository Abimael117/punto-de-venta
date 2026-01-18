document.addEventListener('DOMContentLoaded', () => {
  const canvas = document.getElementById('ventasChart');
  const subtitle = document.getElementById('chartSubtitle');

  const elStock = document.getElementById('alertStock');
  const elCxc   = document.getElementById('alertCxc');
  const elCorte = document.getElementById('alertCorte');

  const tabs = Array.from(document.querySelectorAll('.dash-tab'));
  if (!canvas || !subtitle || !tabs.length) return;

  // --------------------------
  // Helpers
  // --------------------------
  const money = (n) => {
    const num = Number(n || 0);
    return num.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
  };

  const daysAgoLabel = (d) => (d === 1 ? '1 día' : `${d} días`);

  function setLoadingAlerts() {
    elStock.innerHTML = `<div class="dash-empty">Cargando…</div>`;
    elCxc.innerHTML   = `<div class="dash-empty">Cargando…</div>`;
    elCorte.innerHTML = `<div class="dash-empty">Cargando…</div>`;
  }

  function renderRows(container, rows, emptyText) {
    if (!rows || rows.length === 0) {
      container.innerHTML = `<div class="dash-empty">${emptyText}</div>`;
      return;
    }

    container.innerHTML = rows.map(r => `
      <div class="dash-row">
        <div class="left">
          <div class="name">${escapeHtml(r.name || '')}</div>
          <div class="meta">${escapeHtml(r.meta || '')}</div>
        </div>
        <div class="dash-pill ${escapeHtml(r.pillClass || '')}">
          ${escapeHtml(r.pill || '')}
        </div>
      </div>
    `).join('');
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  // --------------------------
  // Chart init
  // --------------------------
  const ctx = canvas.getContext('2d');

  const chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [],
      datasets: [{
        label: 'Ventas',
        data: [],
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (c) => ` ${money(c.raw)}`
          }
        }
      },
      scales: {
        y: {
          ticks: {
            callback: (v) => money(v)
          }
        }
      }
    }
  });

  // --------------------------
  // Data (API + fallback demo)
  // --------------------------
  function demoData(mode) {
    if (mode === 'week') {
      return {
        chart: { title: 'Últimos 7 días', labels: ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'], values: [950, 1200, 780, 1400, 1600, 900, 1100] },
        alerts: {
          stock: [
            { name: 'Coca-Cola 600ml', meta: 'Stock: 3 | Mín: 10', pill: 'Crítico', pillClass: 'danger' },
            { name: 'Sabritas clásicas', meta: 'Stock: 5 | Mín: 12', pill: 'Bajo', pillClass: 'warn' }
          ],
          cxc: [
            { name: 'Juan Pérez', meta: `${daysAgoLabel(5)} vencido`, pill: money(450), pillClass: 'danger' },
            { name: 'María López', meta: `${daysAgoLabel(2)} vencido`, pill: money(120), pillClass: 'warn' }
          ],
          corte: { pendiente: true, fecha: 'Ayer' }
        }
      };
    }

    if (mode === 'month') {
      const labels = Array.from({length: 30}, (_, i) => String(i + 1));
      const values = labels.map(() => Math.floor(400 + Math.random() * 1600));
      return {
        chart: { title: 'Mes actual (día a día)', labels, values },
        alerts: { stock: [], cxc: [], corte: { pendiente: false } }
      };
    }

    // year
    return {
      chart: { title: 'Últimos 12 meses', labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'], values: [8200,9200,7800,10100,11000,9800,12000,11500,10800,9900,12500,13000] },
      alerts: {
        stock: [{ name: 'Harina 1kg', meta: 'Stock: 2 | Mín: 8', pill: 'Crítico', pillClass: 'danger' }],
        cxc: [],
        corte: { pendiente: false }
      }
    };
  }

  async function fetchDashboardData(mode) {
    // 👇 Cambia esta ruta a la que uses en tu proyecto
    const url = `/dashboard/data?mode=${encodeURIComponent(mode)}`;

    try {
      const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const json = await res.json();
      if (!json || json.ok !== true) throw new Error('Respuesta inválida');
      return json.data;
    } catch (e) {
      // Si aún no existe el endpoint, caemos a demo para que el UI funcione
      return demoData(mode);
    }
  }

  function applyChart(mode, chartData) {
    subtitle.textContent = chartData.title || '';

    // Cambiamos tipo según modo (se ve más natural)
    chart.config.type = (mode === 'week') ? 'line' : 'bar';

    chart.data.labels = chartData.labels || [];
    chart.data.datasets[0].data = chartData.values || [];

    chart.update();
  }

  function applyAlerts(alerts) {
    // STOCK
    const stockRows = (alerts.stock || []).slice(0, 5);
    renderRows(elStock, stockRows, '✅ Todo bien: no hay productos críticos.');

    // CxC VENCIDAS
    const cxcRows = (alerts.cxc || []).slice(0, 5);
    renderRows(elCxc, cxcRows, '✅ Sin cuentas vencidas. Así se vive en paz.');

    // CORTE PENDIENTE
    if (alerts.corte && alerts.corte.pendiente) {
      elCorte.innerHTML = `
        <div class="dash-row">
          <div class="left">
            <div class="name">Corte pendiente</div>
            <div class="meta">Fecha: ${escapeHtml(alerts.corte.fecha || 'día anterior')}</div>
          </div>
          <div class="dash-pill danger">Atender</div>
        </div>
      `;
    } else {
      elCorte.innerHTML = `<div class="dash-empty">✅ Corte al día.</div>`;
    }
  }

  async function load(mode) {
    setLoadingAlerts();

    // UI tabs
    tabs.forEach(t => t.classList.toggle('active', t.dataset.mode === mode));

    const data = await fetchDashboardData(mode);

    applyChart(mode, data.chart || { title:'', labels:[], values:[] });
    applyAlerts(data.alerts || { stock:[], cxc:[], corte:{pendiente:false} });
  }

  // Eventos
  tabs.forEach(btn => {
    btn.addEventListener('click', () => load(btn.dataset.mode));
  });

  // Default
  load('week');
});
