document.addEventListener('DOMContentLoaded', () => {

  // =========================
  // ELEMENTOS BASE
  // =========================
  const input = document.getElementById('codigoArticulo');
  const tbody = document.querySelector('#tablaAjuste tbody');

  const ajFecha = document.getElementById('ajFecha');
  const ajNotas = document.getElementById('ajNotas');

  const kpiItems = document.getElementById('kpiItems');
  const kpiNeto  = document.getElementById('kpiNeto');

  const btnGuardar  = document.getElementById('btnGuardar');
  const btnCancelar = document.getElementById('btnCancelar');

  // =========================
  // MODAL
  // =========================
  const modal      = document.getElementById('modalAjuste');
  const btnCerrar  = document.getElementById('btnCerrarAjuste');
  const btnCerrar2 = document.getElementById('btnCerrarAjuste2');
  const btnAplicar = document.getElementById('btnAplicarAjuste');

  const mCodigo = document.getElementById('mCodigo');
  const mDesc   = document.getElementById('mDesc');
  const mStock  = document.getElementById('mStock');
  const mFisico = document.getElementById('mFisico');
  const mHint   = document.getElementById('mHint');

  // =========================
  // STATE
  // =========================
  let searchTimeout = null;

  // catálogo visible (tabla)
  let items = [];

  // ajustes aplicados { articulo_id -> {stock_fisico, ajuste, costo} }
  const ajustes = new Map();

  // artículo seleccionado para modal
  let articuloActual = null;

  const money = (n) => Number(n || 0).toFixed(2);

  function nowLocalDT() {
    const d = new Date();
    const pad = (x) => String(x).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }
  if (ajFecha && !ajFecha.value) ajFecha.value = nowLocalDT();

  async function safeJson(r){ try { return await r.json(); } catch { return null; } }

  // =========================
  // KPIs
  // =========================
  function setKPIs(){
    const det = Array.from(ajustes.values());

    if (kpiItems) kpiItems.innerText = String(det.length);

    const neto = det.reduce((acc, x) => {
      const dif = Number(x.ajuste || 0);
      const imp = Math.abs(dif) * Number(x.costo || 0);
      return acc + (dif >= 0 ? imp : -imp);
    }, 0);

    if (kpiNeto) kpiNeto.innerText = `$${money(neto)}`;
  }

  // =========================
  // RENDER TABLA (ALINEADO A COLUMNAS)
  // =========================
  function render(){
    if (!tbody) return;

    tbody.innerHTML = '';

    if (!items.length){
      const tr = document.createElement('tr');
      tr.innerHTML = `<td class="prov-empty" colspan="7">No hay artículos</td>`;
      tbody.appendChild(tr);
      setKPIs();
      return;
    }

    items.forEach(a => {
      const id = Number(a.id);
      const act = Number(a.stock || 0);
      const costo = Number(a.precio_compra || 0);

      const adj = ajustes.get(id);

      const stockFisico = adj ? Number(adj.stock_fisico || 0) : null;
      const dif = adj ? Number(adj.ajuste || 0) : null;

      const imp  = (adj && dif !== null) ? (Math.abs(dif) * costo) : null;
      const sign = (dif !== null && dif >= 0) ? '+' : '−';
      const cls  = (dif === null) ? '' : (dif >= 0 ? 'ok' : 'bad');

      const tr = document.createElement('tr');

      // 🔥 IMPORTANTE: aquí el ORDEN de <td> coincide 1:1 con el <thead>
      tr.innerHTML = `
        <!-- 1 Código -->
        <td>${a.codigo || ''}</td>

        <!-- 2 Descripción -->
        <td>${a.descripcion || a.nombre || ''}</td>

        <!-- 3 Existencia actual -->
        <td class="num">${money(act)}</td>

        <!-- 4 Stock físico -->
        <td class="num strong">${stockFisico === null ? '—' : money(stockFisico)}</td>

        <!-- 5 Ajuste -->
        <td class="num strong">
          ${dif === null ? '—' : `<span class="aj-pill ${cls}">${sign}${money(Math.abs(dif))}</span>`}
        </td>

        <!-- 6 Importe -->
        <td class="num strong">${imp === null ? '—' : `$${money(imp)}`}</td>

        <!-- 7 Acción -->
        <td class="actions">
          <button class="btn primary invadj-mini btnAjustar" data-id="${id}">Ajustar</button>
          <button class="btn secondary invadj-mini btnLimpiar" data-id="${id}" ${adj ? '' : 'disabled'}>Limpiar</button>
        </td>
      `;

      tbody.appendChild(tr);
    });

    // botones Ajustar
    tbody.querySelectorAll('.btnAjustar').forEach(b => {
      b.addEventListener('click', () => {
        const id = Number(b.dataset.id);
        const art = items.find(x => Number(x.id) === id);
        if (!art) return;

        articuloActual = art;

        const act = Number(art.stock || 0);
        const adj = ajustes.get(id);

        mCodigo.innerText = art.codigo || '—';
        mDesc.innerText   = art.descripcion || art.nombre || '—';
        mStock.innerText  = money(act);

        mFisico.value = adj ? String(adj.stock_fisico) : '';
        updateHint();

        openModal();
        setTimeout(() => mFisico?.focus(), 60);
      });
    });

    // botones Limpiar
    tbody.querySelectorAll('.btnLimpiar').forEach(b => {
      b.addEventListener('click', () => {
        const id = Number(b.dataset.id);
        if (!id) return;

        ajustes.delete(id);
        render();
      });
    });

    setKPIs();
  }

  // =========================
  // MODAL
  // =========================
  function openModal(){
    if (modal) modal.style.display = 'flex';
  }

  function closeModal(){
    if (modal) modal.style.display = 'none';
    articuloActual = null;
  }

  function updateHint(){
    if (!mHint) return;

    if (!articuloActual) {
      mHint.innerText = 'Ajuste: —';
      return;
    }

    const act = Number(articuloActual.stock || 0);
    const fis = parseFloat((mFisico?.value || '0').replace(',', '.')) || 0;
    const dif = fis - act;
    const sign = dif >= 0 ? '+' : '−';

    mHint.innerText = `Ajuste: ${sign}${money(Math.abs(dif))}`;
  }

  function aplicar(){
    if (!articuloActual) return;

    const id = Number(articuloActual.id);
    const act = Number(articuloActual.stock || 0);
    const fis = parseFloat((mFisico?.value || '').replace(',', '.'));

    if (Number.isNaN(fis)) {
      alert('Stock físico inválido');
      mFisico?.focus();
      return;
    }

    const dif = Number((fis - act).toFixed(2));
    if (Math.abs(dif) < 0.00001) {
      alert('No hay cambio (ajuste = 0).');
      mFisico?.focus();
      return;
    }

    const costo = Number(articuloActual.precio_compra || 0);

    ajustes.set(id, {
      articulo_id: id,
      stock_actual: act,
      stock_fisico: Number(fis.toFixed(2)),
      ajuste: dif,
      costo: Number(costo.toFixed(2)),
      codigo: articuloActual.codigo,
      descripcion: articuloActual.descripcion || articuloActual.nombre || ''
    });

    closeModal();
    render();
    input?.focus();
  }

  // =========================
  // DATA LOAD
  // =========================
  async function cargarArticulos(q = ''){
    const r = await fetch(`/operaciones/ajuste-inventario/articulos?q=${encodeURIComponent(q)}`);
    const j = await safeJson(r);

    if (!r.ok || !j || j.ok !== true){
      alert(j?.msg || 'No se pudieron cargar artículos');
      return;
    }

    items = (j.data || []).map(x => ({
      ...x,
      id: Number(x.id),
      stock: Number(x.stock || 0),
      precio_compra: Number(x.precio_compra || 0),
    }));

    render();
  }

  // =========================
  // GUARDAR
  // =========================
  async function guardar(){
    const det = Array.from(ajustes.values());

    if (!det.length){
      alert('No hay artículos ajustados');
      return;
    }

    const payload = {
      cabecera: {
        fecha: ajFecha?.value || null,
        notas: (ajNotas?.value || '').trim()
      },
      detalle: det.map(x => ({
        articulo_id: x.articulo_id,
        stock_actual: x.stock_actual,
        stock_fisico: x.stock_fisico
      }))
    };

    const r = await fetch('/operaciones/ajuste-inventario/guardar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const j = await safeJson(r);

    if (!r.ok || !j || j.ok !== true){
      alert(j?.msg || 'No se pudo guardar');
      return;
    }

    alert(`✅ Ajuste guardado (#${j.id})`);
    location.href = '/operaciones';
  }

  // =========================
  // EVENTOS
  // =========================
  input?.addEventListener('input', () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      const q = (input.value || '').trim();
      cargarArticulos(q);
    }, 220);
  });

  mFisico?.addEventListener('input', updateHint);

  if (btnAplicar) btnAplicar.onclick = aplicar;
  if (btnCerrar)  btnCerrar.onclick  = closeModal;
  if (btnCerrar2) btnCerrar2.onclick = closeModal;


  if (btnGuardar)  btnGuardar.onclick  = guardar;
  if (btnCancelar) btnCancelar.onclick = () => location.reload();

  document.addEventListener('keydown', (e) => {
    if (modal && modal.style.display === 'flex') {
      if (e.key === 'Enter')  { e.preventDefault(); aplicar(); }
      if (e.key === 'Escape') { e.preventDefault(); closeModal(); }
      return;
    }

    if (e.key === 'F3')     { e.preventDefault(); guardar(); }
    if (e.key === 'Escape') { e.preventDefault(); location.reload(); }
  });

  // INIT
  input?.focus();
  cargarArticulos('');
});
