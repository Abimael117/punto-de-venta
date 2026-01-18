document.addEventListener('DOMContentLoaded', () => {

  let filaSel = null;

  const tbody = document.querySelector('.tabla-articulos tbody');
  const infoPanel = document.querySelector('.detalle .info');

  const btnAgregar  = document.getElementById('btnAgregar');
  const btnEditar   = document.getElementById('btnEditar');
  const btnEliminar = document.getElementById('btnEliminar');
  const btnRecargar = document.getElementById('btnRecargar');
  const inputBuscar = document.getElementById('buscarArticulo');

  if (!tbody || !infoPanel) return;

  // =========================
  // SELECCIÓN FILA (delegación)
  // =========================
  tbody.addEventListener('click', (e) => {
    const tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    seleccionarFila(tr);
  });

  function seleccionarFila(tr) {
    tbody.querySelectorAll('tr').forEach(x => x.classList.remove('selected'));
    tr.classList.add('selected');
    filaSel = tr;

    const codigo = tr.dataset.codigo || tr.children[0]?.innerText || '—';
    const nombre = tr.dataset.nombre || tr.children[1]?.innerText || '—';
    const stock  = Number(tr.dataset.stock || 0).toFixed(2);
    const pv     = Number(tr.dataset.precio_venta || 0).toFixed(2);

    infoPanel.innerHTML = `
      <p><strong>Código:</strong> ${codigo}</p>
      <p><strong>Nombre:</strong> ${nombre}</p>
      <p><strong>Existencia:</strong> ${stock}</p>
      <p><strong>Precio:</strong> $${pv}</p>
    `;
  }

  // =========================
  // BUSCADOR
  // =========================
  inputBuscar?.addEventListener('input', () => {
    const t = (inputBuscar.value || '').toLowerCase();

    tbody.querySelectorAll('tr[data-id]').forEach(tr => {
      const codigo = (tr.dataset.codigo || '').toLowerCase();
      const nombre = (tr.dataset.nombre || '').toLowerCase();
      tr.style.display = (codigo.includes(t) || nombre.includes(t)) ? '' : 'none';
    });
  });

  // =========================
  // BOTONES
  // =========================
  btnAgregar?.addEventListener('click', () => abrirModal(false));
  btnEditar?.addEventListener('click', () => {
    if (!filaSel) return alert('Selecciona un artículo');
    abrirModal(true);
  });

  btnEliminar?.addEventListener('click', () => {
    if (!filaSel) return alert('Selecciona un artículo');
    const id = filaSel.dataset.id;
    if (confirm('¿Eliminar este artículo?')) {
      location.href = `/operaciones/articulos/eliminar?id=${encodeURIComponent(id)}`;
    }
  });

  btnRecargar?.addEventListener('click', () => location.reload());

  // Atajos
  document.addEventListener('keydown', (e) => {
    // si hay un modal abierto, no estorbar
    if (document.querySelector('.modal.art-modal')) return;

    if (e.key === 'F3') { e.preventDefault(); abrirModal(false); }
    if (e.key === 'F4') { e.preventDefault(); if (!filaSel) return alert('Selecciona un artículo'); abrirModal(true); }
    if (e.key === 'F5') { e.preventDefault(); location.reload(); }
    if (e.key === 'F6') { e.preventDefault(); if (!filaSel) return alert('Selecciona un artículo'); btnEliminar.click(); }
  });

  // =========================
  // MODAL (Agregar/Editar)
  // =========================
  function abrirModal(esEditar) {

    const row = filaSel;

    const id = esEditar ? (row?.dataset?.id || '') : '';
    const codigo = esEditar ? (row?.dataset?.codigo || '') : '';
    const nombre = esEditar ? (row?.dataset?.nombre || '') : '';
    const precio_compra = esEditar ? (row?.dataset?.precio_compra || '0') : '0';
    const precio_venta  = esEditar ? (row?.dataset?.precio_venta  || '0') : '0';

    const categoria_id = esEditar ? (row?.dataset?.categoria_id || '') : '';
    const unidad_id    = esEditar ? (row?.dataset?.unidad_id || '') : '';

    const modal = document.createElement('div');
    modal.className = 'modal art-modal';
    modal.style.display = 'flex';

    // options HTML que ya tienes
    const cats = document.getElementById('selectCategorias')?.innerHTML || '';
    const unis = document.getElementById('selectUnidades')?.innerHTML || '';

    modal.innerHTML = `
      <div class="modal-window" style="width:860px; max-width:96%;">
        <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center;">
          <div style="font-weight:900;">${esEditar ? 'Editar artículo' : 'Agregar artículo'}</div>
          <button type="button" class="prov-close" data-close="1">✕</button>
        </div>

        <form method="POST" action="/operaciones/articulos/guardar" class="modal-body">
          ${esEditar ? `<input type="hidden" name="id" value="${id}">` : ''}

          <div class="form-row">
            <div class="form-group" style="flex:1;">
              <label>Código</label>
              <input name="codigo" required value="${escapeHtml(codigo)}">
            </div>

            <div class="form-group" style="flex:2;">
              <label>Descripción</label>
              <input name="nombre" required value="${escapeHtml(nombre)}">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group" style="flex:1;">
              <label>Categoría</label>
              <select name="categoria_id" required id="mCat">${cats}</select>
            </div>

            <div class="form-group" style="flex:1;">
              <label>Unidad</label>
              <select name="unidad_id" required id="mUni">${unis}</select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group" style="flex:1;">
              <label>Precio compra</label>
              <input name="precio_compra" type="number" step="0.01" value="${escapeAttr(precio_compra)}">
            </div>

            <div class="form-group" style="flex:1;">
              <label>Precio venta</label>
              <input name="precio_venta" type="number" step="0.01" value="${escapeAttr(precio_venta)}">
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn primary">💾 Guardar</button>
            <button type="button" class="btn danger" data-close="1">❌ Cancelar</button>
          </div>
        </form>
      </div>
    `;

    document.body.appendChild(modal);

    // set selects
    const selCat = modal.querySelector('#mCat');
    const selUni = modal.querySelector('#mUni');
    if (selCat && categoria_id) selCat.value = categoria_id;
    if (selUni && unidad_id) selUni.value = unidad_id;

    // close
    modal.addEventListener('click', (e) => {
      if (e.target === modal) modal.remove();
      if (e.target?.dataset?.close === '1') modal.remove();
    });

    document.addEventListener('keydown', escHandler);
    function escHandler(ev){
      if (ev.key === 'Escape') {
        ev.preventDefault();
        modal.remove();
        document.removeEventListener('keydown', escHandler);
      }
    }

    // foco
    setTimeout(() => {
      const i = modal.querySelector('input[name="codigo"]');
      i?.focus();
      if (esEditar) i?.select();
    }, 60);
  }

  function escapeHtml(s){
    return String(s || '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }
  function escapeAttr(s){ return escapeHtml(s); }

});
