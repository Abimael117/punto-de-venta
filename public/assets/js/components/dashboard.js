document.addEventListener('DOMContentLoaded', async () => {
  const canvas   = document.getElementById('ventasChart');
  const subtitle = document.getElementById('chartSubtitle');

  const elStock = document.getElementById('alertStock');
  const elCxc   = document.getElementById('alertCxc');
  const elCorte = document.getElementById('alertCorte');

  const tabs = Array.from(document.querySelectorAll('.dash-tab'));
  if (!canvas || !subtitle || !tabs.length) return;

  // =========================
  // OFFLINE: Chart.js debe venir LOCAL desde dashboard.php
  // (Ej: /assets/vendor/chartjs/chart.umd.min.js)
  // =========================
  if (!window.Chart) {
    console.error('Chart.js no está cargado. Revisa que exista el script local en dashboard.php');
    subtitle.textContent = 'Error: falta Chart.js local';
    return;
  }

  // =========================
  // Helpers
  // =========================
  const money = (n) =>
    Number(n || 0).toLocaleString('es-MX', {
      style: 'currency',
      currency: 'MXN'
    });

  const daysAgoLabel = (d) => (d === 1 ? '1 día' : `${d} días`);

  function setLoadingAlerts() {
    elStock.innerHTML = `<div class="dash-empty">Cargando…</div>`;
    elCxc.innerHTML   = `<div class="dash-empty">Cargando…</div>`;
    elCorte.innerHTML = `<div class="dash-empty">Cargando…</div>`;
  }

  function escapeHtml(str) {
    return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
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

  // =========================
  // Chart INIT (BARRAS SIEMPRE)
  // =========================
  const ctx = canvas.getContext('2d');

  const chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: [],
      datasets: [{
        label: 'Ventas',
        data: [],
        backgroundColor: 'rgba(37, 99, 235, 0.6)',
        borderColor: 'rgba(37, 99, 235, 1)',
        borderWidth: 1,
        borderRadius: 8,
        maxBarThickness: 42
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (c) => money(c.raw)
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

  // =========================
  // Demo data (fallback)
  // =========================
  function demoData(mode) {
    if (mode === 'week') {
      return {
        chart: {
          title: 'Últimos 7 días',
          labels: ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'],
          values: [950, 1200, 780, 1400, 1600, 900, 1100]
        },
        alerts: {
          stock: [
            { name: 'Coca-Cola 600ml', meta: 'Stock: 3 | Mín: 10', pill: 'Crítico', pillClass: 'danger' }
          ],
          cxc: [],
          corte: { pendiente: false }
        }
      };
    }

    if (mode === 'month') {
      const labels = Array.from({ length: 30 }, (_, i) => String(i + 1));
      const values = labels.map(() => Math.floor(400 + Math.random() * 1600));
      return {
        chart: { title: 'Mes actual (día a día)', labels, values },
        alerts: { stock: [], cxc: [], corte: { pendiente: false } }
      };
    }

    // year
    return {
      chart: {
        title: 'Últimos 12 meses',
        labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        values: [8200,9200,7800,10100,11000,9800,12000,11500,10800,9900,12500,13000]
      },
      alerts: { stock: [], cxc: [], corte: { pendiente: false } }
    };
  }

  async function fetchDashboardData(mode) {
    try {
      const res = await fetch(`/dashboard/data?mode=${encodeURIComponent(mode)}`, {
        headers: { 'Accept': 'application/json' }
      });
      const json = await res.json();
      if (json && json.ok) return json.data;
    } catch (e) {
      // offline o endpoint caído -> demo
    }
    return demoData(mode);
  }

  function applyChart(chartData) {
    subtitle.textContent = chartData.title || '';
    chart.data.labels = chartData.labels || [];
    chart.data.datasets[0].data = chartData.values || [];
    chart.update();
  }

  function applyAlerts(alerts) {
    renderRows(elStock, alerts.stock || [], '✅ Todo bien.');
    renderRows(elCxc, alerts.cxc || [], '✅ Sin cuentas vencidas.');

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
    applyChart((data && data.chart) ? data.chart : {});
    applyAlerts((data && data.alerts) ? data.alerts : {});
  }

  // Eventos
  tabs.forEach(btn => {
    btn.addEventListener('click', () => load(btn.dataset.mode));
  });

  // Default
  load('week');
});
