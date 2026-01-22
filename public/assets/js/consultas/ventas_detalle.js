document.addEventListener('DOMContentLoaded', () => {
  const $ = (sel) => document.querySelector(sel);

  const elDesde   = $('#vdDesde');
  const elHasta   = $('#vdHasta');
  const elCredito = $('#vdCredito');
  const elQ       = $('#vdQ');

  const btnBuscar  = $('#vdBtnBuscar');
  const btnLimpiar = $('#vdBtnLimpiar');

  const tbody = $('#vdTbody');

  const modal      = $('#vdModal');
  const modalMeta  = $('#vdModalMeta');
  const modalTbody = $('#vdModalTbody');
  const modalCerrar  = $('#vdModalCerrar');
  const modalCerrar2 = $('#vdModalCerrar2');

  // Defaults: últimos 7 días
  const hoy = new Date();
  const hace7 = new Date();
  hace7.setDate(hoy.getDate() - 7);

  const fmt = (d) => {
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  };

  if (elDesde && !elDesde.value) elDesde.value = fmt(hace7);
  if (elHasta && !elHasta.value) elHasta.value = fmt(hoy);

  function money(n){
    const x = Number(n || 0);
    return x.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function badgeCredito(esCredito){
    if (Number(esCredito) === 1) return `<span class="badge credito">CRÉDITO</span>`;
    return `<span class="badge contado">CONTADO</span>`;
  }

  async function cargar() {
    tbody.innerHTML = `<tr><td class="vd-empty" colspan="9">Cargando...</td></tr>`;

    const params = new URLSearchParams({
      desde: elDesde.value || '',
      hasta: elHasta.value || '',
      credito: elCredito.value || 'all',
      q: elQ.value.trim()
    });

    try {
      const res = await fetch(`/consultas/ventas-detalle/listar?${params.toString()}`, {
        headers: { 'Accept': 'application/json' }
      });

      const json = await res.json();
      if (!res.ok || !json.ok) throw new Error(json.msg || 'Error');

      const rows = Array.isArray(json.data) ? json.data : [];
      if (!rows.length) {
        tbody.innerHTML = `<tr><td class="vd-empty" colspan="9">Sin resultados.</td></tr>`;
        return;
      }

      // Render: agrupar visualmente por venta_id (repetidos -> celdas en blanco)
      let lastVenta = null;
      let html = '';

      for (const r of rows) {
        const ventaId = r.venta_id;
        const nuevaVenta = (ventaId !== lastVenta);

        const fecha = nuevaVenta ? (r.fecha || '') : '';
        const folio = nuevaVenta ? (r.folio || '') : '';
        const cliente = nuevaVenta
          ? `${r.cliente_nombre || '—'} <span class="muted">(${r.cliente_codigo || 'S/C'})</span>`
          : '';
        const tipo = nuevaVenta ? badgeCredito(r.es_credito) : '';
        const total = nuevaVenta ? `$ ${money(r.total_venta)}` : '';

        const art = `${r.articulo_nombre || '—'} <span class="muted">(${r.articulo_codigo || 'S/C'})</span>`;

        html += `
          <tr class="vd-row" data-venta-id="${ventaId}">
            <td>${fecha}</td>
            <td>${folio}</td>
            <td>${cliente}</td>
            <td>${tipo}</td>
            <td title="${(r.articulo_nombre||'')}" >${art}</td>
            <td class="num">${money(r.cantidad)}</td>
            <td class="num">$ ${money(r.precio)}</td>
            <td class="num">$ ${money(r.importe)}</td>
            <td class="num">${total}</td>
          </tr>
        `;

        lastVenta = ventaId;
      }

      tbody.innerHTML = html;

      // Click -> modal detalle por venta
      document.querySelectorAll('.vd-row').forEach(tr => {
        tr.addEventListener('click', () => {
          const id = Number(tr.dataset.ventaId || 0);
          if (id > 0) abrirDetalle(id);
        });
      });

    } catch (e) {
      tbody.innerHTML = `<tr><td class="vd-empty error" colspan="9">Error al cargar.</td></tr>`;
      // console.error(e);
    }
  }

  async function abrirDetalle(ventaId){
    modal.style.display = 'flex';
    modalMeta.innerHTML = `<div class="vd-empty">Cargando...</div>`;
    modalTbody.innerHTML = `<tr><td class="vd-empty" colspan="5">Cargando...</td></tr>`;

    try{
      const res = await fetch(`/consultas/ventas-detalle/detalle?venta_id=${ventaId}`, {
        headers: { 'Accept': 'application/json' }
      });
      const json = await res.json();
      if (!res.ok || !json.ok) throw new Error(json.msg || 'Error');

      const pack = json.data || {};
      const v = pack.venta || null;
      const items = Array.isArray(pack.items) ? pack.items : [];

      if (!v) {
        modalMeta.innerHTML = `<div class="vd-empty">No se encontró la venta.</div>`;
        modalTbody.innerHTML = `<tr><td class="vd-empty" colspan="5">Sin detalle.</td></tr>`;
        return;
      }

      modalMeta.innerHTML = `
        <div class="vd-meta-grid">
          <div><div class="k">Folio</div><div class="v">${v.folio || '—'}</div></div>
          <div><div class="k">Fecha</div><div class="v">${v.fecha || '—'}</div></div>
          <div><div class="k">Cliente</div><div class="v">${v.cliente_nombre || '—'} <span class="muted">(${v.cliente_codigo || 'S/C'})</span></div></div>
          <div><div class="k">Tipo</div><div class="v">${badgeCredito(v.es_credito)}</div></div>
          <div><div class="k">Total</div><div class="v">$ ${money(v.total_venta)}</div></div>
        </div>
      `;

      if (!items.length) {
        modalTbody.innerHTML = `<tr><td class="vd-empty" colspan="5">Sin artículos.</td></tr>`;
        return;
      }

      modalTbody.innerHTML = items.map(it => `
        <tr>
          <td>${it.articulo_codigo || '—'}</td>
          <td title="${(it.articulo_nombre||'')}" >${it.articulo_nombre || '—'}</td>
          <td class="num">${money(it.cantidad)}</td>
          <td class="num">$ ${money(it.precio)}</td>
          <td class="num">$ ${money(it.importe)}</td>
        </tr>
      `).join('');

    }catch(e){
      modalMeta.innerHTML = `<div class="vd-empty error">Error al obtener detalle.</div>`;
      modalTbody.innerHTML = `<tr><td class="vd-empty error" colspan="5">Error al cargar.</td></tr>`;
    }
  }

  function cerrarModal(){
    modal.style.display = 'none';
  }

  // Events
  btnBuscar.addEventListener('click', cargar);
  btnLimpiar.addEventListener('click', () => {
    elQ.value = '';
    elCredito.value = 'all';
    elDesde.value = fmt(hace7);
    elHasta.value = fmt(hoy);
    cargar();
  });

  elQ.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') cargar();
  });

  modalCerrar.addEventListener('click', cerrarModal);
  modalCerrar2.addEventListener('click', cerrarModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) cerrarModal();
  });

  // init
  cargar();
});
