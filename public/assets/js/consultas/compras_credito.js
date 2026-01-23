document.addEventListener('DOMContentLoaded', () => {

  const $ = (id) => document.getElementById(id);

  const body = $('ccBody');

  // filtros
  const fProveedor = $('fProveedor');
  const fEstatus   = $('fEstatus');
  const fDesde     = $('fDesde');
  const fHasta     = $('fHasta');
  const fQ         = $('fQ');

  // resumen
  const rDocs = $('rDocs');
  const rTotal = $('rTotal');
  const rSaldo = $('rSaldo');
  const rPendiente = $('rPendiente');

  // botones
  const btnRefrescar = $('btnRefrescar');
  const btnVerDetalle = $('btnVerDetalle');
  const btnAbonar = $('btnAbonar');
  const btnMarcarPagada = $('btnMarcarPagada');

  // modal detalle
  const modalDetalle = $('modalDetalleCxp');
  const btnCerrarDetalle = $('btnCerrarDetalle');
  const btnCerrarDetalle2 = $('btnCerrarDetalle2');

  // detalle fields
  const dProveedor = $('dProveedor');
  const dFolio = $('dFolio');
  const dConcepto = $('dConcepto');
  const dFecha = $('dFecha');
  const dVence = $('dVence');
  const dTotal = $('dTotal');
  const dSaldo = $('dSaldo');
  const dEstatus = $('dEstatus');
  const dItems = $('dItems');
  const dPagos = $('dPagos');
  const dNoCompra = $('dNoCompra');
  const dNoPagos = $('dNoPagos');

  // modal abono
  const modalAbono = $('modalAbonoCxp');
  const btnCerrarAbono = $('btnCerrarAbono');
  const btnCancelarAbono = $('btnCancelarAbono');
  const btnGuardarAbono = $('btnGuardarAbono');

  const aProveedor = $('aProveedor');
  const aSaldo = $('aSaldo');
  const aFecha = $('aFecha');
  const aMonto = $('aMonto');
  const aMetodo = $('aMetodo');
  const aReferencia = $('aReferencia');
  const aNotas = $('aNotas');
  const aAfectaCaja = $('aAfectaCaja');

  let rows = [];
  let selectedIndex = -1;

  const money = (n) => Number(n || 0).toFixed(2);

  function toYmd(dateObj){
    const d = dateObj instanceof Date ? dateObj : new Date(dateObj);
    const pad = (x) => String(x).padStart(2,'0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  }

  function today() {
    return toYmd(new Date());
  }

  function daysAgo(n){
    const d = new Date();
    d.setDate(d.getDate() - Number(n || 0));
    return toYmd(d);
  }

  // ✅ DEFAULT DE FECHAS: desde 7 días antes hasta hoy (solo si están vacías)
  function initDefaultDates(){
    if (fHasta && !fHasta.value) fHasta.value = today();
    if (fDesde && !fDesde.value) fDesde.value = daysAgo(7);
  }

  async function safeJson(r){
    try { return await r.json(); } catch { return null; }
  }

  function isOpen(el){
    return !!el && el.style.display !== 'none' && el.style.display !== '';
  }

  function closeModal(el){
    if (el) el.style.display = 'none';
  }

  function openModal(el){
    if (el) el.style.display = 'flex';
  }

  function currentRow(){
    if (selectedIndex < 0 || selectedIndex >= rows.length) return null;
    return rows[selectedIndex];
  }

  function render(){
    if (!body) return;
    body.innerHTML = '';

    rows.forEach((r, idx) => {
      const est = (r.estatus || '').toUpperCase();
      const cls = est === 'PENDIENTE' ? 'st-pendiente' : 'st-pagada';

      body.innerHTML += `
        <tr data-index="${idx}" class="${idx===selectedIndex?'selected':''}">
          <td>${r.proveedor || ''}</td>
          <td>${r.folio || ''}</td>
          <td>${r.fecha || ''}</td>
          <td>${r.vence || ''}</td>
          <td style="text-align:right;">$${money(r.total)}</td>
          <td style="text-align:right;"><strong>$${money(r.saldo)}</strong></td>
          <td><span class="cc-status ${cls}">${est || '-'}</span></td>
        </tr>
      `;
    });
  }

  async function listar(){
    const qs = new URLSearchParams({
      proveedor_id: fProveedor?.value || '',
      estatus: fEstatus?.value || '',
      desde: fDesde?.value || '',
      hasta: fHasta?.value || '',
      q: fQ?.value || ''
    });

    const r = await fetch(`/consultas/compras-credito/listar?${qs.toString()}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const j = await safeJson(r);
    if (!r.ok || !j || j.ok !== true) {
      alert(j?.msg || 'No se pudo cargar');
      return;
    }

    rows = j.rows || [];
    selectedIndex = rows.length ? 0 : -1;

    const res = j.resumen || {};
    rDocs && (rDocs.innerText = res.docs ?? 0);
    rTotal && (rTotal.innerText = money(res.total));
    rSaldo && (rSaldo.innerText = money(res.saldo));
    rPendiente && (rPendiente.innerText = money(res.saldo_pendiente));

    render();
  }

  // seleccionar fila
  body?.addEventListener('click', (e) => {
    const tr = e.target.closest('tr[data-index]');
    if (!tr) return;
    selectedIndex = parseInt(tr.dataset.index, 10);
    render();
  });

  body?.addEventListener('dblclick', () => abrirDetalle());

  // filtros => refrescar
  [fProveedor, fEstatus, fDesde, fHasta].forEach(el => el?.addEventListener('change', listar));
  let t = null;
  fQ?.addEventListener('input', () => {
    if (t) clearTimeout(t);
    t = setTimeout(listar, 250);
  });

  btnRefrescar?.addEventListener('click', listar);
  btnVerDetalle?.addEventListener('click', abrirDetalle);
  btnAbonar?.addEventListener('click', abrirAbono);
  btnMarcarPagada?.addEventListener('click', marcarPagada);

  // teclas
  document.addEventListener('keydown', (e) => {

    // F5 refrescar
    if (e.key === 'F5') { e.preventDefault(); listar(); return; }

    // Enter detalle (si no estás escribiendo)
    if (e.key === 'Enter') {
      const tag = (document.activeElement?.tagName || '').toLowerCase();
      if (['input','textarea','select'].includes(tag)) return;
      if (isOpen(modalAbono) || isOpen(modalDetalle)) return;
      e.preventDefault();
      abrirDetalle();
    }

    // F3 abonar
    if (e.key === 'F3') {
      if (isOpen(modalDetalle)) return;
      e.preventDefault();
      abrirAbono();
    }

    // DEL marcar pagada
    if (e.key === 'Delete') {
      const tag = (document.activeElement?.tagName || '').toLowerCase();
      if (['input','textarea','select'].includes(tag)) return;
      if (isOpen(modalAbono) || isOpen(modalDetalle)) return;
      e.preventDefault();
      marcarPagada();
    }

    // ESC cierra modales
    if (e.key === 'Escape') {
      if (isOpen(modalAbono)) closeModal(modalAbono);
      if (isOpen(modalDetalle)) closeModal(modalDetalle);
    }
  });

  // =========================
  // DETALLE
  // =========================
  async function abrirDetalle(){
    const row = currentRow();
    if (!row) { alert('Selecciona una cuenta'); return; }

    const r = await fetch(`/consultas/compras-credito/detalle?id=${encodeURIComponent(row.id)}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });

    const j = await safeJson(r);
    if (!r.ok || !j || j.ok !== true) {
      alert(j?.msg || 'No se pudo cargar el detalle');
      return;
    }

    const data = j.data || {};
    const cxp = data.cxp || {};
    const items = data.detalle || [];
    const pagos = data.pagos || [];
    const pagosEnabled = !!data.pagos_enabled;

    dProveedor && (dProveedor.textContent = cxp.proveedor || '-');
    dFolio && (dFolio.textContent = cxp.folio || '-');
    dConcepto && (dConcepto.textContent = cxp.concepto || '-');
    dFecha && (dFecha.textContent = cxp.fecha || '-');
    dVence && (dVence.textContent = cxp.vence || '-');
    dTotal && (dTotal.textContent = `$${money(cxp.total)}`);
    dSaldo && (dSaldo.textContent = `$${money(cxp.saldo)}`);
    dEstatus && (dEstatus.textContent = (cxp.estatus || '-'));

    // items
    dItems && (dItems.innerHTML = '');
    if (items.length) {
      dNoCompra && (dNoCompra.style.display = 'none');
      items.forEach(it => {
        const cant = Number(it.cantidad || 0);
        const costo = Number(it.costo || 0);
        const imp = Number(it.impuesto_monto || 0);
        const importe = (cant * costo) + imp;

        dItems.innerHTML += `
          <tr>
            <td>${it.codigo || ''}</td>
            <td>${it.descripcion || ''}</td>
            <td style="text-align:right;">${cant}</td>
            <td style="text-align:right;">${money(costo)}</td>
            <td style="text-align:right;">${money(importe)}</td>
          </tr>
        `;
      });
    } else {
      dNoCompra && (dNoCompra.style.display = 'block');
    }

    // pagos
    dPagos && (dPagos.innerHTML = '');
    if (pagosEnabled && pagos.length) {
      dNoPagos && (dNoPagos.style.display = 'none');
      pagos.forEach(p => {
        dPagos.innerHTML += `
          <tr>
            <td>${p.fecha || ''}</td>
            <td>${p.metodo || ''}</td>
            <td style="text-align:right;">$${money(p.monto)}</td>
          </tr>
        `;
      });
    } else {
      dNoPagos && (dNoPagos.style.display = 'block');
    }

    openModal(modalDetalle);
  }

  function cerrarDetalle(){
    closeModal(modalDetalle);
  }

  btnCerrarDetalle?.addEventListener('click', cerrarDetalle);
  btnCerrarDetalle2?.addEventListener('click', cerrarDetalle);
  modalDetalle?.addEventListener('click', (e) => { if (e.target === modalDetalle) cerrarDetalle(); });

  // =========================
  // ABONO
  // =========================
  function abrirAbono(){
    const row = currentRow();
    if (!row) { alert('Selecciona una cuenta'); return; }
    if ((row.estatus || '').toUpperCase() !== 'PENDIENTE' || Number(row.saldo||0) <= 0) {
      alert('Esta cuenta ya está pagada');
      return;
    }

    aProveedor && (aProveedor.textContent = row.proveedor || '-');
    aSaldo && (aSaldo.textContent = money(row.saldo));
    aFecha && (aFecha.value = today());
    aMonto && (aMonto.value = '');
    aMetodo && (aMetodo.value = 'EFECTIVO');
    aReferencia && (aReferencia.value = '');
    aNotas && (aNotas.value = '');
    aAfectaCaja && (aAfectaCaja.checked = false);

    openModal(modalAbono);
    setTimeout(() => aMonto?.focus(), 50);
  }

  function cerrarAbono(){
    closeModal(modalAbono);
  }

  btnCerrarAbono?.addEventListener('click', cerrarAbono);
  btnCancelarAbono?.addEventListener('click', cerrarAbono);
  modalAbono?.addEventListener('click', (e) => { if (e.target === modalAbono) cerrarAbono(); });

  btnGuardarAbono?.addEventListener('click', async () => {
    const row = currentRow();
    if (!row) return;

    const monto = parseFloat(aMonto?.value || '0') || 0;
    if (monto <= 0) { alert('Monto inválido'); aMonto?.focus(); return; }

    const payload = {
      cxp_id: row.id,
      monto,
      fecha: aFecha?.value || today(),
      metodo: aMetodo?.value || 'EFECTIVO',
      referencia: (aReferencia?.value || '').trim(),
      notas: (aNotas?.value || '').trim(),
      afecta_caja: !!aAfectaCaja?.checked
    };

    const r = await fetch('/consultas/compras-credito/abonar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const j = await safeJson(r);
    if (!r.ok || !j || j.ok !== true) {
      alert(j?.msg || 'No se pudo registrar abono');
      return;
    }

    cerrarAbono();
    await listar();
    alert('✅ Abono registrado');
  });

  // =========================
  // MARCAR PAGADA
  // =========================
  async function marcarPagada(){
    const row = currentRow();
    if (!row) { alert('Selecciona una cuenta'); return; }

    const ok = confirm(`¿Marcar como PAGADA el folio "${row.folio}"? Esto pondrá saldo=0.`);
    if (!ok) return;

    const r = await fetch('/consultas/compras-credito/marcarPagada', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cxp_id: row.id })
    });

    const j = await safeJson(r);
    if (!r.ok || !j || j.ok !== true) {
      alert(j?.msg || 'No se pudo marcar pagada');
      return;
    }

    await listar();
    alert('✅ Cuenta marcada como pagada');
  }

  // init
  initDefaultDates(); // 👈 aquí se ponen desde/hasta por default
  listar();
});
