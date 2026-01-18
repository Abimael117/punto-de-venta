document.addEventListener('DOMContentLoaded', () => {

  const btnRecargar = document.getElementById('btnRecargar');
  const btnEstado   = document.getElementById('btnEstado');
  const btnSalir    = document.getElementById('btnSalir');

  const cpBuscar = document.getElementById('cpBuscar');
  const tbody    = document.querySelector('#tablaCP tbody');

  const kpiTotal  = document.getElementById('kpiTotal');
  const kpiPagado = document.getElementById('kpiPagado');
  const kpiSaldo  = document.getElementById('kpiSaldo');

  // Modal
  const modal      = document.getElementById('modalCP');
  const btnCerrar  = document.getElementById('btnCerrarCP');
  const btnCerrar2 = document.getElementById('btnCerrarCP2');

  const dId        = document.getElementById('dId');
  const dProveedor = document.getElementById('dProveedor');
  const dTotal     = document.getElementById('dTotal');
  const dPagado    = document.getElementById('dPagado');
  const dSaldo     = document.getElementById('dSaldo');

  const pMetodo = document.getElementById('pMetodo');
  const pMonto  = document.getElementById('pMonto');
  const pRef    = document.getElementById('pRef');
  const btnPagar = document.getElementById('btnPagar');

  const pagosBody = document.querySelector('#tablaPagos tbody');

  let estado = 'PENDIENTE'; // PENDIENTE | PAGADA | TODOS
  let currentCuentaId = null;

  const money = (n) => Number(n || 0).toFixed(2);
  async function safeJson(r){ try{ return await r.json(); } catch{ return null; } }

  function setKPIs(rows){
    const total  = rows.reduce((a,x)=>a+Number(x.total||0),0);
    const pagado = rows.reduce((a,x)=>a+Number(x.pagado||0),0);
    const saldo  = rows.reduce((a,x)=>a+Number(x.saldo||0),0);
    kpiTotal.innerText  = `$${money(total)}`;
    kpiPagado.innerText = `$${money(pagado)}`;
    kpiSaldo.innerText  = `$${money(saldo)}`;
  }

  function render(rows){
    tbody.innerHTML = '';

    if(!rows.length){
      const tr = document.createElement('tr');
      tr.innerHTML = `<td class="prov-empty" colspan="9">No hay deudas</td>`;
      tbody.appendChild(tr);
      setKPIs([]);
      return;
    }

    setKPIs(rows);

    rows.forEach(r=>{
      const tr = document.createElement('tr');

      const saldo = Number(r.saldo||0);
      const saldoColor = saldo <= 0.009 ? '#15803d' : '#b91c1c';

      const vence = (r.vence || '').toString();
      const est = (r.estatus || '').toString().toUpperCase();

      tr.dataset.text = `${r.id} ${r.compra_id||''} ${r.proveedor_nombre||''} ${vence} ${est} ${r.total} ${r.pagado} ${r.saldo}`.toLowerCase();

      tr.innerHTML = `
        <td>#${r.id}</td>
        <td>${r.compra_id ? `#${r.compra_id}` : '—'}</td>
        <td>${r.proveedor_nombre || 'Proveedor'}</td>
        <td>${vence || '—'}</td>
        <td style="font-weight:900;">${est}</td>
        <td style="text-align:right;">$${money(r.total)}</td>
        <td style="text-align:right;">$${money(r.pagado)}</td>
        <td style="text-align:right; font-weight:900; color:${saldoColor};">$${money(r.saldo)}</td>
        <td><button class="btn secondary cp-mini" data-id="${r.id}">Ver / Pagar</button></td>
      `;
      tbody.appendChild(tr);
    });

    tbody.querySelectorAll('button.cp-mini').forEach(b=>{
      b.addEventListener('click', ()=>abrirDetalle(Number(b.dataset.id)));
    });
  }

  function filtrar(txt){
    const t = (txt||'').toLowerCase();
    tbody.querySelectorAll('tr').forEach(tr=>{
      const x = (tr.dataset.text || tr.innerText || '').toLowerCase();
      tr.style.display = x.includes(t) ? '' : 'none';
    });
  }

  async function cargar(){
    try{
      const r = await fetch(`/operaciones/cuentas-por-pagar/listar?estado=${encodeURIComponent(estado)}`);
      const j = await safeJson(r);

      if(!r.ok || !j || j.ok !== true){
        alert(j?.msg || 'No se pudo cargar');
        return;
      }

      render(j.data || []);
      filtrar(cpBuscar.value);
    }catch(e){
      console.error(e);
      alert('Error cargando');
    }
  }

  function openModal(){ modal.style.display='flex'; }
  function closeModal(){ modal.style.display='none'; currentCuentaId=null; }

  function renderPagos(list){
    pagosBody.innerHTML = '';
    if(!list.length){
      const tr = document.createElement('tr');
      tr.innerHTML = `<td class="prov-empty" colspan="4">Sin pagos</td>`;
      pagosBody.appendChild(tr);
      return;
    }

    list.forEach(p=>{
      const tr = document.createElement('tr');
      const fecha = (p.created_at || '').toString().slice(0,19).replace('T',' ');
      tr.innerHTML = `
        <td>${fecha || ''}</td>
        <td>${(p.metodo || '')}</td>
        <td style="text-align:right; font-weight:900;">$${money(p.monto)}</td>
        <td>${p.referencia || ''}</td>
      `;
      pagosBody.appendChild(tr);
    });
  }

  async function abrirDetalle(cuentaId){
    currentCuentaId = cuentaId;

    try{
      const r = await fetch(`/operaciones/cuentas-por-pagar/detalle?cuenta_id=${encodeURIComponent(cuentaId)}`);
      const j = await safeJson(r);

      if(!r.ok || !j || j.ok !== true){
        alert(j?.msg || 'No se pudo cargar detalle');
        return;
      }

      const cxp = j.data?.cuenta;
      const pagos = j.data?.pagos || [];

      dId.innerText        = `#${cxp.id}`;
      dProveedor.innerText = cxp.proveedor_nombre || 'Proveedor';
      dTotal.innerText     = `$${money(cxp.total)}`;
      dPagado.innerText    = `$${money(cxp.pagado)}`;
      dSaldo.innerText     = `$${money(cxp.saldo)}`;

      pMonto.value = '';
      pRef.value   = '';

      renderPagos(pagos);
      openModal();
      setTimeout(()=>pMonto.focus(), 80);

    }catch(e){
      console.error(e);
      alert('Error detalle');
    }
  }

  async function pagar(){
    if(!currentCuentaId) return;

    const monto = parseFloat((pMonto.value || '0').replace(',', '.')) || 0;
    const metodo = (pMetodo.value || 'EFECTIVO').toUpperCase().trim();
    const referencia = (pRef.value || '').trim();

    if(monto <= 0){
      alert('Monto inválido');
      pMonto.focus();
      return;
    }

    try{
      const r = await fetch('/operaciones/cuentas-por-pagar/abonar', {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify({
          cuenta_id: currentCuentaId,
          monto: Number(monto.toFixed(2)),
          metodo,
          referencia
        })
      });

      const j = await safeJson(r);

      if(!r.ok || !j || j.ok !== true){
        alert(j?.msg || 'No se pudo registrar pago');
        return;
      }

      await abrirDetalle(currentCuentaId);
      await cargar();
      alert('✅ Pago registrado');

    }catch(e){
      console.error(e);
      alert('Error pagando');
    }
  }

  function toggleEstado(){
    // PENDIENTE -> TODOS -> PAGADA -> PENDIENTE
    if(estado === 'PENDIENTE') estado = 'TODOS';
    else if(estado === 'TODOS') estado = 'PAGADA';
    else estado = 'PENDIENTE';

    btnEstado.innerText = (estado === 'PENDIENTE') ? '✅ PENDIENTES'
                    : (estado === 'PAGADA') ? '💚 PAGADAS'
                    : '📌 TODOS';

    cargar();
  }

  // eventos
  btnRecargar.onclick = cargar;
  btnEstado.onclick   = toggleEstado;
  btnSalir.onclick    = () => location.href = '/operaciones';

  cpBuscar.addEventListener('input', ()=>filtrar(cpBuscar.value));

  btnPagar.onclick = pagar;

  btnCerrar.onclick  = closeModal;
  btnCerrar2.onclick = closeModal;

  modal.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });

  document.addEventListener('keydown', (e)=>{
    if(modal.style.display === 'flex'){
      if(e.key === 'Escape'){ e.preventDefault(); closeModal(); }
      return;
    }
    if(e.key === 'Escape'){ e.preventDefault(); location.href='/operaciones'; }
  });

  // init
  btnEstado.innerText = '✅ PENDIENTES';
  cargar();
});
