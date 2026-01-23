document.addEventListener('DOMContentLoaded', () => {

  // Base
  const input     = document.getElementById('codigoArticulo');
  const tbody     = document.getElementById('detalleCompra');
  const totalSpan = document.getElementById('totalCompra');

  const subSpan   = document.getElementById('subtotalCompra');
  const impSpan   = document.getElementById('impuestosCompra');

  // Cabecera
  const compraFolio     = document.getElementById('compraFolio'); // (ya no existe en vista, pero no truena)
  const compraFechaHora = document.getElementById('compraFechaHora');
  const compraTipo      = document.getElementById('compraTipo');
  const compraMetodo    = document.getElementById('compraMetodo');
  const compraConCaja   = document.getElementById('compraConCaja');
  const compraNotas     = document.getElementById('compraNotas');

  // Proveedor (SICAR)
  const proveedorSelect = document.getElementById('proveedorCompra');
  const proveedorTexto  = document.getElementById('proveedorTexto');
  const proveedorId     = document.getElementById('proveedorId');

  const modalProv        = document.getElementById('modalProveedores');
  const btnAbrirProv     = document.getElementById('btnAbrirProveedores');
  const btnCerrarProv    = document.getElementById('btnCerrarProveedores');
  const buscarProv       = document.getElementById('buscarProveedor');
  const tablaProvBody    = document.querySelector('#tablaProveedores tbody');
  const btnSelProv       = document.getElementById('btnSeleccionarProveedor');
  const btnAgregarProv   = document.getElementById('btnAgregarProveedor');

  // Modal nuevo proveedor
  const modalNuevoProv   = document.getElementById('modalAgregarProveedor');
  const formNuevoProv    = document.getElementById('formAgregarProveedor');
  const btnCerrarNuevo   = document.getElementById('btnCerrarModalProveedor');
  const btnCancelarNuevo = document.getElementById('btnCancelarProveedor');

  let proveedorFilaSel = null;

  let detalle = [];
  let scanTimeout = null;
  let codigoPendiente = null;

  // Modal cantidad state
  let articuloActual = null;
  let editIndex = null; // ✅ cuando no es null, estamos editando una línea existente

  // Remover (selección)
  let filaSeleccionadaIndex = -1;
  const btnRemover = document.getElementById('btnRemover');

  // Helpers
  const money = (n) => Number(n || 0).toFixed(2);

  function nowLocalDT() {
    const d = new Date();
    const pad = (x) => String(x).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
  }

  async function safeJson(r){
    try { return await r.json(); } catch { return null; }
  }

  // =========================================================
  // ✅ CLICK EN CUALQUIER PARTE = ENFOCAR INPUT ESCÁNER
  // =========================================================
  function isOpen(el) {
    return !!el && el.style.display !== 'none' && el.style.display !== '';
  }

  function isAnyModalOpen() {
    const modalCantidad  = document.getElementById('modalCantidadArticulo');
    const modalArt       = document.getElementById('modalAgregarArticulo');
    const modalConf      = document.getElementById('modalConfirmarArticulo');

    return isOpen(modalProv) || isOpen(modalNuevoProv) || isOpen(modalCantidad) || isOpen(modalArt) || isOpen(modalConf);
  }

  function shouldIgnoreClickTarget(target) {
    if (!target) return true;
    if (target.closest('input, textarea, select, [contenteditable="true"]')) return true;
    if (target.closest('button, a, .btn, [role="button"]')) return true;
    if (target.closest('#detalleCompra')) return true;
    if (target.closest('[data-no-refocus="1"]')) return true;
    return false;
  }

  document.addEventListener('click', (e) => {
    if (!input) return;
    if (isAnyModalOpen()) return;

    const t = e.target;
    if (shouldIgnoreClickTarget(t)) return;

    const sel = window.getSelection?.();
    if (sel && sel.toString && sel.toString().trim().length > 0) return;

    input.focus();
    try { input.select(); } catch {}
  });

  // =========================
  // Init cabecera
  // =========================
  if (compraFechaHora && !compraFechaHora.value) compraFechaHora.value = nowLocalDT();

  function syncTipoUI(){
    const tipo = (compraTipo?.value || 'CONTADO').toUpperCase();

    if (tipo === 'CREDITO') {
      if (compraMetodo) compraMetodo.disabled = true;
      if (compraConCaja) {
        compraConCaja.checked = false;
        compraConCaja.disabled = true;
      }
    } else {
      if (compraMetodo) compraMetodo.disabled = false;
      if (compraConCaja) compraConCaja.disabled = false;
    }
  }

  compraTipo?.addEventListener('change', syncTipoUI);
  syncTipoUI();

  // Focus inicial (escáner)
  input?.focus();

  // =========================
  // MODAL PROVEEDORES
  // =========================
  function abrirModalProveedores() {
    if (!modalProv) return;
    modalProv.style.display = 'flex';
    proveedorFilaSel = null;

    setTimeout(() => {
      if (buscarProv) {
        buscarProv.value = '';
        buscarProv.focus();
      }
      filtrarProveedores('');
    }, 50);
  }

  function cerrarModalProveedores() {
    if (!modalProv) return;
    modalProv.style.display = 'none';
    proveedorFilaSel = null;
    input?.focus();
    input?.select?.();
  }

  btnAbrirProv && (btnAbrirProv.onclick = abrirModalProveedores);
  proveedorTexto && (proveedorTexto.onclick = abrirModalProveedores);
  btnCerrarProv && (btnCerrarProv.onclick = cerrarModalProveedores);

  modalProv?.addEventListener('click', (e) => {
    if (e.target === modalProv) cerrarModalProveedores();
  });

  function seleccionarFilaProveedor(tr) {
    if (!tablaProvBody) return;
    tablaProvBody.querySelectorAll('tr').forEach(r => r.classList.remove('selected'));
    tr.classList.add('selected');
    proveedorFilaSel = tr;
  }

  tablaProvBody?.addEventListener('click', (e) => {
    const tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    seleccionarFilaProveedor(tr);
  });

  tablaProvBody?.addEventListener('dblclick', (e) => {
    const tr = e.target.closest('tr[data-id]');
    if (!tr) return;
    seleccionarFilaProveedor(tr);
    aplicarProveedorSeleccionado();
  });

  function filtrarProveedores(texto) {
    if (!tablaProvBody) return;
    const t = (texto || '').toLowerCase();

    tablaProvBody.querySelectorAll('tr').forEach(tr => {
      if (!tr.dataset.id) return;
      const txt = (tr.dataset.text || tr.innerText).toLowerCase();
      tr.style.display = txt.includes(t) ? '' : 'none';
    });
  }

  buscarProv && (buscarProv.oninput = () => filtrarProveedores(buscarProv.value));

  function aplicarProveedorSeleccionado() {
    if (!proveedorFilaSel) {
      alert('Selecciona un proveedor');
      return;
    }

    const id = proveedorFilaSel.dataset.id;
    const text = proveedorFilaSel.dataset.text;

    if (proveedorId) proveedorId.value = id;
    if (proveedorTexto) proveedorTexto.value = text;
    if (proveedorSelect) proveedorSelect.value = id;

    cerrarModalProveedores();
  }

  btnSelProv && (btnSelProv.onclick = aplicarProveedorSeleccionado);

  // =========================
  // MODAL NUEVO PROVEEDOR
  // =========================
  function abrirModalNuevoProveedor() {
    if (!modalNuevoProv) return;
    modalNuevoProv.style.display = 'flex';
    formNuevoProv?.reset();
    setTimeout(() => document.getElementById('mpNombre')?.focus(), 50);
  }

  function cerrarModalNuevoProveedor() {
    if (!modalNuevoProv) return;
    modalNuevoProv.style.display = 'none';

    if (isOpen(modalProv)) {
      setTimeout(() => buscarProv?.focus(), 50);
    } else {
      input?.focus();
      input?.select?.();
    }
  }

  btnAgregarProv && (btnAgregarProv.onclick = abrirModalNuevoProveedor);
  btnCerrarNuevo && (btnCerrarNuevo.onclick = cerrarModalNuevoProveedor);
  btnCancelarNuevo && (btnCancelarNuevo.onclick = cerrarModalNuevoProveedor);

  modalNuevoProv?.addEventListener('click', (e) => {
    if (e.target === modalNuevoProv) cerrarModalNuevoProveedor();
  });

  formNuevoProv?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const fd = new FormData(formNuevoProv);

    try {
      const r = await fetch('/operaciones/compras/guardarProveedor', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      const res = await safeJson(r);

      if (!r.ok || !res || res.ok !== true) {
        alert(res?.msg || 'Error al guardar proveedor');
        return;
      }

      const prov = res.proveedor;

      if (proveedorSelect) {
        const opt = document.createElement('option');
        opt.value = prov.id;
        opt.textContent = prov.nombre;
        proveedorSelect.appendChild(opt);
      }

      if (tablaProvBody) {
        const tr = document.createElement('tr');
        tr.dataset.id = prov.id;
        tr.dataset.text = prov.nombre;

        const numero = tablaProvBody.querySelectorAll('tr[data-id]').length + 1;

        tr.innerHTML = `<td>${numero}</td><td>${prov.nombre}</td>`;
        tablaProvBody.prepend(tr);

        seleccionarFilaProveedor(tr);
      }

      cerrarModalNuevoProveedor();
      aplicarProveedorSeleccionado();

    } catch {
      alert('Error al guardar proveedor');
    }
  });

  // =========================
  // BOTONES
  // =========================
  document.getElementById('btnGuardar')?.addEventListener('click', guardar);
  document.getElementById('btnCancelar')?.addEventListener('click', () => location.reload());
  btnRemover?.addEventListener('click', removerSeleccionado);

  // Selección fila detalle
  tbody?.addEventListener('click', (e) => {
    const tr = e.target.closest('tr[data-index]');
    if (!tr) return;

    tbody.querySelectorAll('tr').forEach(r => r.classList.remove('selected'));
    tr.classList.add('selected');

    filaSeleccionadaIndex = parseInt(tr.dataset.index, 10);
  });

  // ✅ Doble click fila detalle => abrir modal cantidad para EDITAR
  tbody?.addEventListener('dblclick', (e) => {
    const tr = e.target.closest('tr[data-index]');
    if (!tr) return;
    const idx = parseInt(tr.dataset.index, 10);
    if (Number.isNaN(idx) || !detalle[idx]) return;

    filaSeleccionadaIndex = idx;
    abrirModalCantidadDesdeDetalle(idx);
  });

  // Teclas POS
  document.addEventListener('keydown', (e) => {

    // F2 abre proveedores
    if (e.key === 'F2') { e.preventDefault(); abrirModalProveedores(); return; }

    // F3 guardar
    if (e.key === 'F3') { e.preventDefault(); guardar(); return; }

    // ESC cancelar
    if (e.key === 'Escape') { e.preventDefault(); location.reload(); return; }

    // DEL remover
    if (e.key === 'Delete') {
      const tag = (document.activeElement?.tagName || '').toLowerCase();
      const estaEscribiendo = tag === 'input' || tag === 'textarea' || tag === 'select';
      if (estaEscribiendo) return;
      if (isAnyModalOpen()) return;
      removerSeleccionado();
    }

    // ✅ ENTER sobre detalle seleccionado => editar cantidad
    if (e.key === 'Enter') {
      if (isAnyModalOpen()) return;

      const tag = (document.activeElement?.tagName || '').toLowerCase();
      const estaEscribiendo = tag === 'input' || tag === 'textarea' || tag === 'select';
      if (estaEscribiendo) return;

      if (filaSeleccionadaIndex >= 0 && detalle[filaSeleccionadaIndex]) {
        e.preventDefault();
        abrirModalCantidadDesdeDetalle(filaSeleccionadaIndex);
        return;
      }
    }

    // si modal proveedor abierto:
    if (isOpen(modalProv)) {
      if (e.key === 'Enter') { e.preventDefault(); aplicarProveedorSeleccionado(); return; }
      if (e.key === 'F3') { e.preventDefault(); abrirModalNuevoProveedor(); return; }
    }

    // si modal nuevo proveedor abierto:
    if (isOpen(modalNuevoProv)) {
      if (e.key === 'Escape') { e.preventDefault(); cerrarModalNuevoProveedor(); return; }
    }
  });

  function removerSeleccionado() {
    if (!detalle.length) { alert('No hay artículos para remover'); return; }

    if (filaSeleccionadaIndex < 0 || filaSeleccionadaIndex >= detalle.length) {
      alert('Selecciona un artículo de la lista para remover');
      return;
    }

    const item = detalle[filaSeleccionadaIndex];
    const ok = confirm(`¿Remover "${item.descripcion}" de la compra?`);
    if (!ok) return;

    detalle.splice(filaSeleccionadaIndex, 1);
    filaSeleccionadaIndex = -1;

    render();
    input?.focus();
    input?.select?.();
  }

  // =========================
  // ESCÁNER (DEBOUNCE)
  // =========================
  input?.addEventListener('input', () => {
    if (scanTimeout) clearTimeout(scanTimeout);

    scanTimeout = setTimeout(() => {
      const codigo = input.value.trim();
      if (!codigo) return;

      input.value = '';
      buscarArticulo(codigo);
    }, 250);
  });

  // =========================
  // BUSCAR ARTÍCULO
  // =========================
  function buscarArticulo(codigo) {
    fetch(`/operaciones/articulos/buscar?codigo=${encodeURIComponent(codigo)}`)
      .then(r => r.json())
      .then(articulo => {

        if (!articulo) {
          codigoPendiente = codigo;
          mostrarConfirmacion(codigo);
          return;
        }

        // ✅ Escaneado => modal en modo NUEVO (sumará si ya existe)
        abrirModalCantidad(articulo, null);
      })
      .catch(err => console.error('Error buscarArticulo:', err));
  }

  // =========================
  // AGREGAR LINEA (modo NUEVO)
  // =========================
  function agregarLineaConCantidad(articulo, cantidad, costo, extras = {}) {

    const item = detalle.find(i => i.articulo_id === articulo.id);

    if (item) {
      item.cantidad += cantidad;
      item.costo = costo;

      item.impuesto_id = extras.impuesto_id ?? item.impuesto_id ?? null;
      item.impuesto_tasa = extras.impuesto_tasa ?? item.impuesto_tasa ?? 0;
      item.impuesto_monto = (extras.impuesto_monto ?? 0) + (item.impuesto_monto ?? 0);

      item.costo_con_impuesto = extras.costo_con_impuesto ?? item.costo_con_impuesto ?? 0;
      item.utilidad_pct = extras.utilidad_pct ?? item.utilidad_pct ?? 0;
      item.precio_venta_sugerido = extras.precio_venta_sugerido ?? item.precio_venta_sugerido ?? 0;
      item.precio_venta = extras.precio_venta ?? item.precio_venta ?? 0;

    } else {
      detalle.push({
        articulo_id: articulo.id,
        codigo: articulo.codigo,
        descripcion: articulo.descripcion,
        cantidad,
        costo,

        impuesto_id: extras.impuesto_id ?? null,
        impuesto_tasa: extras.impuesto_tasa ?? 0,
        impuesto_monto: extras.impuesto_monto ?? 0,
        costo_con_impuesto: extras.costo_con_impuesto ?? 0,
        utilidad_pct: extras.utilidad_pct ?? 0,
        precio_venta_sugerido: extras.precio_venta_sugerido ?? 0,
        precio_venta: extras.precio_venta ?? 0
      });
    }

    filaSeleccionadaIndex = detalle.length - 1;
    render();
  }

  // =========================
  // ✅ EDITAR DESDE DETALLE
  // =========================
  function abrirModalCantidadDesdeDetalle(idx){
    const item = detalle[idx];
    if (!item) return;

    // armamos “articulo” compatible con tu modal
    const articulo = {
      id: item.articulo_id,
      codigo: item.codigo,
      descripcion: item.descripcion,
      precio_compra: item.costo
    };

    abrirModalCantidad(articulo, idx, item);
  }

  // =========================
  // MODAL CANTIDAD (NUEVO / EDITAR)
  // =========================
  function abrirModalCantidad(articulo, idx = null, itemExistente = null) {

    articuloActual = articulo;
    editIndex = (typeof idx === 'number') ? idx : null;

    const modal = document.getElementById('modalCantidadArticulo');
    if (!modal) return;
    modal.style.display = 'flex';

    document.getElementById('mcDescripcion').innerText = articulo.descripcion;
    document.getElementById('mcCodigo').innerText = articulo.codigo;

    const cantidadInput   = document.getElementById('mcCantidad');
    const costoInput      = document.getElementById('mcCosto');
    const impuestoSelect  = document.getElementById('mcImpuesto');
    const utilidadInput   = document.getElementById('mcUtilidad');

    const costoRealInput  = document.getElementById('mcCostoReal');
    const pvSugInput      = document.getElementById('mcPrecioVentaSug');
    const pvInput         = document.getElementById('mcPrecioVenta');

    const subtotalSpan    = document.getElementById('mcSubtotal');
    const impMontoSpan    = document.getElementById('mcImpuestoMonto');
    const importeSpan     = document.getElementById('mcImporte');

    // ✅ Prefill: nuevo vs editar
    if (itemExistente) {
      cantidadInput.value = Number(itemExistente.cantidad || 1);
      costoInput.value = Number(itemExistente.costo || 0);

      // impuesto
      impuestoSelect.value = itemExistente.impuesto_id ? String(itemExistente.impuesto_id) : '';

      // utilidad / pv
      utilidadInput.value = Number(itemExistente.utilidad_pct ?? 20);

      pvInput.value = money(itemExistente.precio_venta ?? 0);
      pvInput.dataset.manual = '1'; // respeta lo que ya tenía
    } else {
      cantidadInput.value = 1;
      costoInput.value = (articulo.precio_compra ?? 0);

      impuestoSelect.value = '';
      utilidadInput.value = 20;

      pvInput.dataset.manual = '';
      pvInput.value = '0.00';
    }

    pvInput.oninput = () => { pvInput.dataset.manual = '1'; };

    setTimeout(() => cantidadInput.focus(), 10);

    cantidadInput.oninput   = recalcular;
    costoInput.oninput      = recalcular;
    impuestoSelect.onchange = recalcular;
    utilidadInput.oninput   = recalcular;

    recalcular();

    function getImpuestoData() {
      const opt = impuestoSelect.options[impuestoSelect.selectedIndex];
      const tasa = parseFloat(opt?.dataset?.tasa || '0') || 0;
      const trasret = (opt?.dataset?.trasret || 'TRAS').toUpperCase();
      const impuesto_id = impuestoSelect.value ? parseInt(impuestoSelect.value) : null;
      return { impuesto_id, tasa, trasret };
    }

    function recalcular() {
      const cant = parseFloat(cantidadInput.value) || 0;
      const costoBase = parseFloat(costoInput.value) || 0;

      const { tasa, trasret } = getImpuestoData();

      const subtotal = cant * costoBase;

      let impuestoMonto = 0;
      if (tasa > 0) {
        impuestoMonto = subtotal * (tasa / 100);
        if (trasret === 'RET') impuestoMonto = impuestoMonto * -1;
      }

      const importe = subtotal + impuestoMonto;
      const costoRealUnit = cant > 0 ? (importe / cant) : 0;

      const utilPct = parseFloat(utilidadInput.value) || 0;
      const pvSug = costoRealUnit * (1 + (utilPct / 100));

      subtotalSpan.innerText = money(subtotal);
      impMontoSpan.innerText = money(impuestoMonto);
      importeSpan.innerText  = money(importe);

      costoRealInput.value = money(costoRealUnit);
      pvSugInput.value = money(pvSug);

      const userManual = pvInput.dataset.manual === '1';
      const pvActual = parseFloat(pvInput.value || '0') || 0;

      if (!userManual || pvActual === 0) {
        pvInput.value = money(pvSug);
      }
    }
  }

  // Confirmación no encontrado
  function mostrarConfirmacion(codigo) {
    const modal = document.getElementById('modalConfirmarArticulo');
    if (!modal) return;
    document.getElementById('codigoNoEncontrado').innerText = codigo;

    modal.style.display = 'flex';

    document.getElementById('btnNoAgregar').onclick = () => {
      modal.style.display = 'none';
      input?.focus();
      input?.select?.();
    };

    document.getElementById('btnSiAgregar').onclick = () => {
      modal.style.display = 'none';
      abrirModalAgregarArticulo(codigo);
    };
  }

  function abrirModalAgregarArticulo(codigo) {
    const modal = document.getElementById('modalAgregarArticulo');
    if (!modal) return;
    modal.style.display = 'flex';

    const inputCodigo = modal.querySelector('input[name="codigo"]');
    inputCodigo.value = codigo;

    setTimeout(() => inputCodigo.focus(), 50);
  }

  document.getElementById('btnCerrarModalArticulo')?.addEventListener('click', () => {
    const modal = document.getElementById('modalAgregarArticulo');
    if (modal) modal.style.display = 'none';
    input?.focus();
    input?.select?.();
  });

  // ✅ Aceptar cantidad (NUEVO o EDITAR)
  document.getElementById('btnAceptarCantidad')?.addEventListener('click', () => {

    const cant = parseFloat(document.getElementById('mcCantidad').value) || 1;
    const costoBase = parseFloat(document.getElementById('mcCosto').value) || 0;

    const impuestoSelect = document.getElementById('mcImpuesto');
    const opt = impuestoSelect.options[impuestoSelect.selectedIndex];
    const impuesto_id = impuestoSelect.value ? parseInt(impuestoSelect.value) : null;
    const impuesto_tasa = parseFloat(opt?.dataset?.tasa || '0') || 0;
    const trasret = (opt?.dataset?.trasret || 'TRAS').toUpperCase();

    const utilidad_pct = parseFloat(document.getElementById('mcUtilidad').value) || 0;

    const subtotal = cant * costoBase;

    let impuesto_monto = 0;
    if (impuesto_tasa > 0) {
      impuesto_monto = subtotal * (impuesto_tasa / 100);
      if (trasret === 'RET') impuesto_monto = impuesto_monto * -1;
    }

    const importe = subtotal + impuesto_monto;
    const costo_con_impuesto = cant > 0 ? (importe / cant) : 0;

    const precio_venta_sugerido = costo_con_impuesto * (1 + (utilidad_pct / 100));

    const pvField = document.getElementById('mcPrecioVenta');
    const precio_venta = parseFloat(pvField.value) || precio_venta_sugerido;

    // ✅ SI EDITAMOS, actualiza la línea seleccionada (no suma, no crea otra)
    if (editIndex !== null && detalle[editIndex]) {
      const it = detalle[editIndex];

      it.cantidad = cant;
      it.costo = costoBase;

      it.impuesto_id = impuesto_id;
      it.impuesto_tasa = impuesto_tasa;
      it.impuesto_monto = impuesto_monto;

      it.costo_con_impuesto = costo_con_impuesto;
      it.utilidad_pct = utilidad_pct;
      it.precio_venta_sugerido = precio_venta_sugerido;
      it.precio_venta = precio_venta;

      filaSeleccionadaIndex = editIndex;
      render();

    } else {
      // modo NUEVO: agrega o suma si ya existe
      agregarLineaConCantidad(
        articuloActual,
        cant,
        costoBase,
        {
          impuesto_id,
          impuesto_tasa,
          impuesto_monto,
          costo_con_impuesto,
          utilidad_pct,
          precio_venta_sugerido,
          precio_venta
        }
      );
    }

    cerrarModalCantidad();
    input?.focus();
    input?.select?.();
  });

  document.getElementById('btnCancelarCantidad')?.addEventListener('click', () => {
    cerrarModalCantidad();
    input?.focus();
    input?.select?.();
  });

  function cerrarModalCantidad() {
    const m = document.getElementById('modalCantidadArticulo');
    if (m) m.style.display = 'none';
    articuloActual = null;
    editIndex = null;
  }

  // Guardar artículo (AJAX)
  document.getElementById('formAgregarArticulo')?.addEventListener('submit', e => {
    e.preventDefault();

    const form = e.target;
    const data = new FormData(form);

    fetch('/operaciones/articulos/guardar', {
      method: 'POST',
      body: data,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
      if (!res || res.ok !== true) {
        alert('Error al guardar el artículo');
        return;
      }

      const modal = document.getElementById('modalAgregarArticulo');
      if (modal) modal.style.display = 'none';
      form.reset();

      buscarArticulo(codigoPendiente);
      codigoPendiente = null;
    })
    .catch(err => console.error('Error guardar artículo:', err));
  });

  // =========================
  // RENDER
  // =========================
  function render() {

    if (!tbody) return;

    tbody.innerHTML = '';

    let subtotal = 0;
    let impuestos = 0;

    detalle.forEach((item, idx) => {

      const cant = Number(item.cantidad || 0);
      const costo = Number(item.costo || 0);

      const sub = cant * costo;
      const imp = Number(item.impuesto_monto || 0);
      const totalLinea = sub + imp;

      subtotal += sub;
      impuestos += imp;

      tbody.innerHTML += `
        <tr data-index="${idx}" class="${idx === filaSeleccionadaIndex ? 'selected' : ''}">
          <td class="col-codigo">${item.codigo}</td>
          <td class="col-desc">${item.descripcion}</td>
          <td class="col-cant" style="text-align:right;">${cant}</td>
          <td class="col-costo" style="text-align:right;">${money(costo)}</td>
          <td class="col-importe" style="text-align:right;">${money(totalLinea)}</td>
        </tr>
      `;
    });

    const total = subtotal + impuestos;

    subSpan && (subSpan.innerText = money(subtotal));
    impSpan && (impSpan.innerText = money(impuestos));
    totalSpan && (totalSpan.innerText = money(total));
  }

  // =========================
  // GUARDAR COMPRA
  // =========================
  async function guardar() {

    if (!proveedorId?.value) {
      alert('Selecciona un proveedor');
      abrirModalProveedores();
      return;
    }

    if (!detalle.length) {
      alert('No hay artículos en la compra');
      return;
    }

    const tipo = (compraTipo?.value || 'CONTADO').toUpperCase();
    const metodo_pago = (compraMetodo?.value || 'EFECTIVO').toUpperCase();
    const pagada_con_caja = !!compraConCaja?.checked && tipo === 'CONTADO';

    const payload = {
      compra: {
        proveedor_id: proveedorId.value,
        fecha_hora: compraFechaHora?.value ? compraFechaHora.value.replace('T',' ') + ':00' : null,
        tipo,
        metodo_pago,
        pagada_con_caja: pagada_con_caja ? 1 : 0,
        notas: (compraNotas?.value || '').trim(),
        subtotal: subSpan?.innerText || 0,
        impuestos_total: impSpan?.innerText || 0,
        total: totalSpan?.innerText || 0
      },
      detalle
    };

    try {
      const r = await fetch('/operaciones/compras/guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const j = await safeJson(r);

      if (!r.ok || !j || j.ok !== true) {
        alert(j?.msg || 'No se pudo guardar la compra');
        return;
      }

      // (folio ya no se muestra, pero no estorba)
      if (compraFolio) compraFolio.value = j.folio || '(auto)';

      alert(`✅ Compra guardada${j.folio ? ` (${j.folio})` : ''}`);
      location.reload();

    } catch (err) {
      console.error(err);
      alert('Error al guardar compra');
    }
  }

  // init
  render();
});
