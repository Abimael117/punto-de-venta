document.addEventListener('DOMContentLoaded', () => {

  const input = document.getElementById('codigoArticulo');
  const tbody = document.querySelector('#tablaInvIni tbody');

  if (!input || !tbody) return;

  const invFecha = document.getElementById('invFecha');
  const invNotas = document.getElementById('invNotas');

  const kpiItems = document.getElementById('kpiItems');
  const kpiTotal = document.getElementById('kpiTotal');

  const btnGuardar = document.getElementById('btnGuardar');
  const btnCancelar = document.getElementById('btnCancelar');

  // Modal cantidad
  const modal = document.getElementById('modalInvIni');
  const btnCerrar = document.getElementById('btnCerrarInvIni');
  const btnCerrar2 = document.getElementById('btnCerrarInvIni2');
  const btnAplicar = document.getElementById('btnAplicarInvIni');

  const mCodigo = document.getElementById('mCodigo');
  const mDesc = document.getElementById('mDesc');
  const mStock = document.getElementById('mStock');
  const mCantidad = document.getElementById('mCantidad');

  // Modal agregar artículo
  const modalAdd = document.getElementById('modalInvAddArt');
  const formAdd = document.getElementById('formInvAddArt');
  const btnCloseAdd = document.getElementById('btnCerrarInvAddArt');
  const btnCancelAdd = document.getElementById('btnInvAddArtCancelar');

  const ia_codigo = document.getElementById('ia_codigo');
  const ia_nombre = document.getElementById('ia_nombre');
  const ia_categoria = document.getElementById('ia_categoria');
  const ia_unidad = document.getElementById('ia_unidad');
  const ia_precio_compra = document.getElementById('ia_precio_compra');
  const ia_precio_venta = document.getElementById('ia_precio_venta');

  let scanTimeout = null;
  let articuloActual = null;
  let detalle = [];
  let codigoPendiente = null;

  const money = (n) => Number(n || 0).toFixed(2);

  function nowLocalDT() {
    const d = new Date();
    const pad = (x) => String(x).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  if (invFecha && !invFecha.value) invFecha.value = nowLocalDT();

  async function safeJson(r) { try { return await r.json(); } catch { return null; } }

  function setKPIs() {
    if (kpiItems) kpiItems.innerText = String(detalle.length);
    const total = detalle.reduce((a, x) => a + (Number(x.cantidad || 0) * Number(x.costo || 0)), 0);
    if (kpiTotal) kpiTotal.innerText = `$${money(total)}`;
  }

  function render() {
    tbody.innerHTML = '';

    if (!detalle.length) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td class="prov-empty" colspan="6">No hay artículos</td>`;
      tbody.appendChild(tr);
      setKPIs();
      return;
    }

    detalle.forEach((it, idx) => {
      const imp = (Number(it.cantidad || 0) * Number(it.costo || 0));
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${it.codigo}</td>
        <td>${it.descripcion}</td>
        <td style="text-align:right;">${money(it.stock_actual)}</td>
        <td style="text-align:right; font-weight:900;">${money(it.cantidad)}</td>
        <td style="text-align:right; font-weight:900;">$${money(imp)}</td>
        <td>
          <button class="btn danger inv-mini" data-i="${idx}">Eliminar</button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    tbody.querySelectorAll('button.inv-mini').forEach(b => {
      b.addEventListener('click', () => {
        const i = Number(b.dataset.i);
        if (Number.isNaN(i)) return;
        const ok = confirm(`¿Eliminar "${detalle[i]?.descripcion}"?`);
        if (!ok) return;
        detalle.splice(i, 1);
        render();
      });
    });

    setKPIs();
  }

  // =================
  // MODAL CANTIDAD
  // =================
  function openModal() { if (modal) modal.style.display = 'flex'; }
  function closeModal() {
    if (modal) modal.style.display = 'none';
    articuloActual = null;
    input.focus();
    input.select();
  }

  // ==========================
  // DEFAULTS PARA SELECTS
  // ==========================
  function setDefaultSelects() {
    // Categoría: "abarrotes"
    if (ia_categoria) {
      const optAbar = [...ia_categoria.options].find(o =>
        (o.textContent || '').trim().toLowerCase() === 'abarrotes'
      );
      ia_categoria.value = optAbar ? optAbar.value : (ia_categoria.value || '');
    }

    // Unidad: "pza"
    if (ia_unidad) {
      const optPza = [...ia_unidad.options].find(o =>
        (o.textContent || '').trim().toLowerCase() === 'pza'
      );
      ia_unidad.value = optPza ? optPza.value : (ia_unidad.value || '');
    }
  }

  // ==========================
  // MODAL AGREGAR ARTICULO
  // ==========================
  function openAddModal(codigo) {
    codigoPendiente = codigo;

    if (modalAdd) modalAdd.style.display = 'flex';

    if (ia_codigo) ia_codigo.value = codigo || '';
    if (ia_nombre) ia_nombre.value = '';
    if (ia_precio_compra) ia_precio_compra.value = '0';
    if (ia_precio_venta) ia_precio_venta.value = '0';

    setDefaultSelects();
    setTimeout(() => ia_nombre?.focus(), 80);
  }

  function closeAddModal() {
    if (modalAdd) modalAdd.style.display = 'none';
    codigoPendiente = null;
    input.focus();
    input.select();
  }

  if (btnCloseAdd) btnCloseAdd.onclick = closeAddModal;
  if (btnCancelAdd) btnCancelAdd.onclick = closeAddModal;

  if (modalAdd) {
    modalAdd.addEventListener('click', (e) => {
      if (e.target === modalAdd) closeAddModal();
    });
  }

  // Guardar artículo por AJAX sin salir
  if (formAdd) {
    formAdd.addEventListener('submit', async (e) => {
      e.preventDefault();

      if (!ia_codigo?.value.trim()) return alert('Código requerido');
      if (!ia_nombre?.value.trim()) return alert('Descripción requerida');
      if (!ia_categoria?.value) return alert('Selecciona una categoría');
      if (!ia_unidad?.value) return alert('Selecciona una unidad');

      const fd = new FormData(formAdd);

      try {
        const r = await fetch('/operaciones/articulos/guardar', {
          method: 'POST',
          body: fd,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const res = await safeJson(r);

        if (!r.ok || !res || res.ok !== true) {
          alert(res?.msg || 'Error al guardar el artículo');
          return;
        }

        const cod = ia_codigo.value.trim();
        closeAddModal();
        setTimeout(() => buscarArticulo(cod), 80);

      } catch (err) {
        console.error(err);
        alert('Error al guardar el artículo');
      }
    });
  }

  // =================
  // BUSCAR ARTICULO
  // =================
  async function buscarArticulo(codigo) {
    const r = await fetch(`/operaciones/articulos/buscar?codigo=${encodeURIComponent(codigo)}`);
    const articulo = await safeJson(r);

    if (!articulo) {
      openAddModal(codigo);
      return;
    }

    articuloActual = articulo;

    if (mCodigo) mCodigo.innerText = articulo.codigo || codigo;
    if (mDesc) mDesc.innerText = articulo.descripcion || articulo.nombre || '—';
    if (mStock) mStock.innerText = money(articulo.stock || 0);

    if (mCantidad) mCantidad.value = '';
    openModal();
    setTimeout(() => mCantidad?.focus(), 60);
  }

  function aplicar() {
    if (!articuloActual) return;

    const cant = parseFloat((mCantidad?.value || '0').replace(',', '.')) || 0;

    if (cant <= 0) {
      alert('Cantidad inválida');
      mCantidad?.focus();
      return;
    }

    const costo = parseFloat(articuloActual.precio_compra || 0) || 0;

    const idx = detalle.findIndex(x => Number(x.articulo_id) === Number(articuloActual.id));
    const row = {
      articulo_id: Number(articuloActual.id),
      codigo: articuloActual.codigo,
      descripcion: articuloActual.descripcion || articuloActual.nombre || 'Artículo',
      stock_actual: Number(articuloActual.stock || 0),
      cantidad: Number(cant.toFixed(2)),
      costo: Number(costo.toFixed(2))
    };

    if (idx >= 0) detalle[idx] = row;
    else detalle.push(row);

    closeModal();
    render();
    input.focus();
    input.select();
  }

  async function guardar() {
    if (!detalle.length) {
      alert('No hay artículos');
      return;
    }

    const payload = {
      cabecera: {
        fecha: invFecha?.value || null,
        notas: (invNotas?.value || '').trim()
      },
      detalle: detalle.map(x => ({
        articulo_id: x.articulo_id,
        cantidad: x.cantidad
      }))
    };

    const r = await fetch('/operaciones/inventario-inicial/guardar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const j = await safeJson(r);

    if (!r.ok || !j || j.ok !== true) {
      alert(j?.msg || 'No se pudo guardar');
      return;
    }

    alert(`✅ Inventario inicial guardado (#${j.id})`);
    location.href = '/operaciones';
  }

  // =========================
  // ESCÁNER (debounce)
  // =========================
  input.addEventListener('input', () => {
    if (scanTimeout) clearTimeout(scanTimeout);
    scanTimeout = setTimeout(() => {
      const codigo = (input.value || '').trim();
      if (!codigo) return;
      input.value = '';
      buscarArticulo(codigo);
    }, 220);
  });

  // Botones modal cantidad
  if (btnAplicar) btnAplicar.onclick = aplicar;
  if (btnCerrar) btnCerrar.onclick = closeModal;
  if (btnCerrar2) btnCerrar2.onclick = closeModal;

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal();
    });
  }

  // Botones principales
  if (btnGuardar) btnGuardar.onclick = guardar;
  if (btnCancelar) btnCancelar.onclick = () => location.reload();

  // =========================
  // HOTKEYS
  // =========================
  function isOpen(el) {
    return !!el && el.style.display !== 'none' && el.style.display !== '';
  }

  document.addEventListener('keydown', (e) => {

    if (isOpen(modalAdd)) {
      if (e.key === 'Escape') { e.preventDefault(); closeAddModal(); }
      return;
    }

    if (isOpen(modal)) {
      if (e.key === 'Enter') { e.preventDefault(); aplicar(); }
      if (e.key === 'Escape') { e.preventDefault(); closeModal(); }
      return;
    }

    if (e.key === 'F3') { e.preventDefault(); guardar(); }
    if (e.key === 'Escape') { e.preventDefault(); location.reload(); }
  });

  // =========================================================
  // ✅ CLICK EN CUALQUIER PARTE = ENFOCAR INPUT ESCÁNER
  // (misma lógica que ventas.js)
  // =========================================================
  function isAnyModalOpen() {
    return isOpen(modal) || isOpen(modalAdd);
  }

  function shouldIgnoreClickTarget(target) {
    if (!target) return true;

    // No robamos foco si el click es en input/select/textarea/editable
    if (target.closest('input, textarea, select, [contenteditable="true"]')) return true;

    // No robamos foco si el click fue en botón/link/btn o rol botón
    if (target.closest('button, a, .btn, [role="button"]')) return true;

    // Excluir algo a mano cuando quieras
    if (target.closest('[data-no-refocus="1"]')) return true;

    return false;
  }

  document.addEventListener('click', (e) => {
    if (isAnyModalOpen()) return;

    const t = e.target;
    if (shouldIgnoreClickTarget(t)) return;

    input.focus();
    input.select();
  });

  input.focus();
  input.select();
  render();
});
