document.addEventListener('DOMContentLoaded', () => {
  const $desde   = document.getElementById('vcDesde');
  const $hasta   = document.getElementById('vcHasta');
  const $estado  = document.getElementById('vcEstado');
  const $q       = document.getElementById('vcQ');

  const $btnBuscar  = document.getElementById('vcBtnBuscar');
  const $btnLimpiar = document.getElementById('vcBtnLimpiar');

  const $tbody   = document.getElementById('vcTbody');

  const $modal   = document.getElementById('vcModal');
  const $cerrar1 = document.getElementById('vcModalCerrar');
  const $cerrar2 = document.getElementById('vcModalCerrar2');

  const $meta        = document.getElementById('vcModalMeta');
  const $creditosTb  = document.getElementById('vcCreditosTbody');
  const $pagosTb     = document.getElementById('vcPagosTbody');
  const $verArt      = document.getElementById('vcBtnVerArticulos');

  let currentCredito = null; // { credito_id, folio, ... }

  function pad(n){ return String(n).padStart(2,'0'); }
  function todayISO(){
    const d = new Date();
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  }
  function firstDayOfMonthISO(){
    const d = new Date();
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-01`;
  }

  function money(v){
    const n = Number(v || 0);
    return n.toLocaleString('es-MX', { style:'currency', currency:'MXN' });
  }

  function safeText(s){
    return String(s ?? '').replace(/[&<>"']/g, (m)=>({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  function badgeHTML(semaforo){
    if (semaforo === 'VENCIDO')    return `<span class="badge bad">VENCIDO</span>`;
    if (semaforo === 'POR_VENCER') return `<span class="badge warn">POR VENCER</span>`;
    return `<span class="badge ok">AL DÍA</span>`;
  }

  function openModal(){
    $modal.style.display = 'flex';
  }
  function closeModal(){
    $modal.style.display = 'none';
    currentCredito = null;
    $verArt.disabled = true;
  }

  async function fetchJSON(url){
    const res = await fetch(url, { headers: { 'Accept':'application/json' } });
    const data = await res.json().catch(()=> ({}));
    if (!res.ok || data.ok === false) {
      throw new Error(data.msg || 'Error');
    }
    return data;
  }

  function setLoading(){
    $tbody.innerHTML = `<tr><td colspan="7" class="vc-empty">Cargando...</td></tr>`;
  }
  function setEmpty(msg){
    $tbody.innerHTML = `<tr><td colspan="7" class="vc-empty">${safeText(msg)}</td></tr>`;
  }

  async function cargar(){
    const desde  = $desde.value || '';
    const hasta  = $hasta.value || '';
    const estado = $estado.value || 'all';
    const q      = ($q.value || '').trim();

    const params = new URLSearchParams({ desde, hasta, estado, q });
    setLoading();

    try{
      const { data } = await fetchJSON(`/consultas/ventas-credito/listar?${params.toString()}`);

      if (!Array.isArray(data) || data.length === 0){
        setEmpty('Sin resultados');
        return;
      }

      $tbody.innerHTML = data.map(r => {
        const cliente = `${safeText(r.cliente_nombre || '')} (${safeText(r.cliente_codigo || '')})`;
        const total   = money(r.credito_total);
        const saldo   = money(r.saldo_total);
        const ncred   = Number(r.n_creditos || 0);
        const vence   = safeText(r.proximo_vencimiento || '—');
        const dias    = Number(r.dias_restantes_min ?? 0);
        const diasTxt = (isNaN(dias) ? '—' : `${dias}`);

        return `
          <tr data-cliente="${Number(r.cliente_id)}" title="Click para ver desglose">
            <td>${cliente}</td>
            <td class="num">${safeText(total)}</td>
            <td class="num">${safeText(saldo)}</td>
            <td class="num">${safeText(ncred)}</td>
            <td>${vence}</td>
            <td class="num">${safeText(diasTxt)}</td>
            <td>${badgeHTML(r.semaforo)}</td>
          </tr>
        `;
      }).join('');

    }catch(err){
      setEmpty(`Error: ${err.message}`);
    }
  }

  function setModalLoading(){
    $meta.innerHTML = '';
    $creditosTb.innerHTML = `<tr><td colspan="7" class="vc-empty">Cargando...</td></tr>`;
    $pagosTb.innerHTML = `<tr><td colspan="4" class="vc-empty">Selecciona un crédito…</td></tr>`;
    currentCredito = null;
    $verArt.disabled = true;
  }

  async function cargarDetalleCliente(clienteId){
    setModalLoading();

    const desde  = $desde.value || '';
    const hasta  = $hasta.value || '';
    const estado = $estado.value || 'all';
    const q      = ($q.value || '').trim();

    const params = new URLSearchParams({ cliente_id: clienteId, desde, hasta, estado, q });

    try{
      const { data } = await fetchJSON(`/consultas/ventas-credito/detalle?${params.toString()}`);

      const c = data.cliente || {};
      const creditos = Array.isArray(data.creditos) ? data.creditos : [];

      $meta.innerHTML = `
        <div>
          <div class="k">Cliente</div>
          <div class="v">${safeText(c.cliente_nombre || '')} (${safeText(c.cliente_codigo || '')})</div>
        </div>
        <div>
          <div class="k">Créditos</div>
          <div class="v">${safeText(c.n_creditos ?? creditos.length)}</div>
        </div>
        <div>
          <div class="k">Total</div>
          <div class="v">${safeText(money(c.credito_total))}</div>
        </div>
        <div>
          <div class="k">Saldo</div>
          <div class="v">${safeText(money(c.saldo_total))}</div>
        </div>
        <div>
          <div class="k">Próx. vence</div>
          <div class="v">${safeText(c.proximo_vencimiento || '—')} &nbsp; ${badgeHTML(c.semaforo || 'AL_DIA')}</div>
        </div>
        <div>
          <div class="k">Días</div>
          <div class="v">${safeText(String(c.dias_restantes_min ?? '—'))}</div>
        </div>
      `;

      if (!creditos.length){
        $creditosTb.innerHTML = `<tr><td colspan="7" class="vc-empty">Sin créditos</td></tr>`;
        return;
      }

      $creditosTb.innerHTML = creditos.map(r => {
        const fecha = safeText(r.fecha || '');
        const folio = safeText(r.folio || '');
        const total = safeText(money(r.venta_total));
        const saldo = safeText(money(r.saldo));
        const vence = safeText(r.fecha_vencimiento || '—');
        const dias  = Number(r.dias_restantes ?? 0);
        const diasTxt = (isNaN(dias) ? '—' : `${dias}`);

        return `
          <tr data-credito="${Number(r.credito_id)}" data-folio="${safeText(r.folio || '')}" title="Click para ver abonos / evidencia">
            <td>${fecha}</td>
            <td>${folio}</td>
            <td class="num">${total}</td>
            <td class="num">${saldo}</td>
            <td>${vence}</td>
            <td class="num">${safeText(diasTxt)}</td>
            <td>${badgeHTML(r.semaforo)}</td>
          </tr>
        `;
      }).join('');

    }catch(err){
      $meta.innerHTML = `<div class="vc-empty">Error: ${safeText(err.message)}</div>`;
      $creditosTb.innerHTML = `<tr><td colspan="7" class="vc-empty">Sin datos</td></tr>`;
    }
  }

  async function cargarPagos(creditoId){
    $pagosTb.innerHTML = `<tr><td colspan="4" class="vc-empty">Cargando...</td></tr>`;

    try{
      const { data } = await fetchJSON(`/consultas/ventas-credito/credito?id=${encodeURIComponent(creditoId)}`);
      const pagos = Array.isArray(data.pagos) ? data.pagos : [];

      if (!pagos.length){
        $pagosTb.innerHTML = `<tr><td colspan="4" class="vc-empty">Sin abonos</td></tr>`;
        return;
      }

      $pagosTb.innerHTML = pagos.map(p => `
        <tr>
          <td>${safeText(p.created_at || '')}</td>
          <td class="num">${safeText(money(p.monto))}</td>
          <td>${safeText(p.metodo || '—')}</td>
          <td>${safeText(p.referencia || '—')}</td>
        </tr>
      `).join('');

    }catch(err){
      $pagosTb.innerHTML = `<tr><td colspan="4" class="vc-empty">Error: ${safeText(err.message)}</td></tr>`;
    }
  }

  function limpiar(){
    $desde.value  = firstDayOfMonthISO();
    $hasta.value  = todayISO();
    $estado.value = 'all';
    $q.value      = '';
    cargar();
  }

  // Defaults iniciales
  if (!$desde.value) $desde.value = firstDayOfMonthISO();
  if (!$hasta.value) $hasta.value = todayISO();

  // Eventos
  $btnBuscar?.addEventListener('click', cargar);
  $btnLimpiar?.addEventListener('click', limpiar);

  $q?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') cargar();
  });

  // Click cliente -> modal
  $tbody?.addEventListener('click', (e) => {
    const tr = e.target.closest('tr[data-cliente]');
    if (!tr) return;

    const clienteId = Number(tr.dataset.cliente || 0);
    if (!clienteId) return;

    openModal();
    cargarDetalleCliente(clienteId);
  });

  // Click crédito dentro del modal -> pagos + habilitar evidencia
  $creditosTb?.addEventListener('click', (e) => {
    const tr = e.target.closest('tr[data-credito]');
    if (!tr) return;

    // UI selected
    $creditosTb.querySelectorAll('tr').forEach(r => r.classList.remove('selected'));
    tr.classList.add('selected');

    const creditoId = Number(tr.dataset.credito || 0);
    if (!creditoId) return;

    currentCredito = {
      credito_id: creditoId,
      folio: tr.dataset.folio || ''
    };

    $verArt.disabled = false;
    cargarPagos(creditoId);
  });

  $cerrar1?.addEventListener('click', closeModal);
  $cerrar2?.addEventListener('click', closeModal);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && $modal?.style.display === 'flex') closeModal();
  });

  $modal?.addEventListener('click', (e) => {
    if (e.target === $modal) closeModal();
  });

  $verArt?.addEventListener('click', () => {
    if (!currentCredito) return;
    const folio = currentCredito.folio || '';
    // Mandamos al detalle de ventas con búsqueda por folio (evidencia)
    location.href = `/consultas/ventas-detalle?q=${encodeURIComponent(folio)}`;
  });

  // Carga inicial
  cargar();
});
