document.addEventListener('DOMContentLoaded', () => {

  // =========================
  // ELEMENTOS BASE
  // =========================
  const input     = document.getElementById('codigoArticulo');
  const tbody     = document.getElementById('detalleVenta');
  const totalSpan = document.getElementById('totalVenta');

  if (!input || !tbody || !totalSpan) return;

  // ===== Cliente =====
  const clienteId     = document.getElementById('clienteId');
  const clienteNombre = document.getElementById('clienteNombre');
  const clienteCodigo = document.getElementById('clienteCodigo');

  // ===== Modal Clientes =====
  const modalClientes          = document.getElementById('modalClientes');
  const btnCliente             = document.getElementById('btnCliente');
  const btnCerrarClientes      = document.getElementById('btnCerrarClientes');
  const btnCancelarClientes    = document.getElementById('btnCancelarClientes');
  const buscarCliente          = document.getElementById('buscarCliente');
  const tablaClientesBody      = document.querySelector('#tablaClientes tbody');
  const btnSeleccionarCliente  = document.getElementById('btnSeleccionarCliente');

  let clienteFilaSel = null;

  // ===== Crear Cliente (modal rápido) =====
  const modalCrearCliente         = document.getElementById('modalCrearCliente');
  const btnCrearCliente           = document.getElementById('btnCrearCliente');
  const btnCerrarCrearCliente     = document.getElementById('btnCerrarCrearCliente');
  const btnCancelarCrearCliente   = document.getElementById('btnCancelarCrearCliente');
  const btnGuardarClienteRapido   = document.getElementById('btnGuardarClienteRapido');

  const cc_nombre    = document.getElementById('cc_nombre');
  const cc_telefono  = document.getElementById('cc_telefono');
  const cc_email     = document.getElementById('cc_email');
  const cc_direccion = document.getElementById('cc_direccion');
  const cc_limite    = document.getElementById('cc_limite');
  const cc_dias      = document.getElementById('cc_dias');
  const cc_permite   = document.getElementById('cc_permite');
  const cc_error     = document.getElementById('cc_error');

  let creandoCliente = false;

  // ===== Modal Cobro =====
  const modalCobro         = document.getElementById('modalCobro');
  const totalCobroSpan     = document.getElementById('totalCobro');
  const cambioCobroSpan    = document.getElementById('cambioCobro');
  const efectivoInput      = document.getElementById('pagoEfectivo');
  const tarjetaInput       = document.getElementById('pagoTarjeta');
  const transferenciaInput = document.getElementById('pagoTransferencia');
  const referenciaInput    = document.getElementById('pagoReferencia');

  const btnToggleCredito   = document.getElementById('btnToggleCredito');
  const creditoPanel       = document.getElementById('creditoPanel');

  const creditoDias        = document.getElementById('creditoDias');
  const creditoLimite      = document.getElementById('creditoLimite');
  const creditoAdeudo      = document.getElementById('creditoAdeudo');
  const creditoDisponible  = document.getElementById('creditoDisponible');
  const creditoVence       = document.getElementById('creditoVence');
  const creditoTotal       = document.getElementById('creditoTotal');
  const creditoWarning     = document.getElementById('creditoWarning');

  // ===== Modal Recuperar Espera =====
  const modalEspera       = document.getElementById('modalEspera');
  const btnCerrarEspera   = document.getElementById('btnCerrarEspera');
  const btnCancelarEspera = document.getElementById('btnCancelarEspera');
  const btnAceptarEspera  = document.getElementById('btnAceptarEspera');
  const tablaEsperaBody   = document.querySelector('#tablaEspera tbody');
  const buscarEspera      = document.getElementById('buscarEspera');

  let esperaFilaSelId = null;

  // ===== ✅ Modal Cantidad =====
  const modalCantidad       = document.getElementById('modalCantidad');
  const btnCerrarCantidad   = document.getElementById('btnCerrarCantidad');
  const btnCancelarCantidad = document.getElementById('btnCancelarCantidad');
  const btnGuardarCantidad  = document.getElementById('btnGuardarCantidad');
  const qtyArticuloTxt      = document.getElementById('qtyArticuloTxt');
  const qtyStockTxt         = document.getElementById('qtyStockTxt');
  const qtyCantidadInput    = document.getElementById('qtyCantidad');
  const qtyError            = document.getElementById('qtyError');
  const btnQtyMenos         = document.getElementById('btnQtyMenos');
  const btnQtyMas           = document.getElementById('btnQtyMas');
  const btnQtyMenos10       = document.getElementById('btnQtyMenos10');
  const btnQtyMas10         = document.getElementById('btnQtyMas10');

  let editQtyIndex = -1;

  // ===== Botones toolbar =====
  const btnCancelar  = document.getElementById('btnCancelar');
  const btnCobrar    = document.getElementById('btnCobrar');
  const btnRemover   = document.getElementById('btnRemover');
  const btnEspera    = document.getElementById('btnEspera');
  const btnRecuperar = document.getElementById('btnRecuperar');

  // Estado
  let detalle = [];
  let scanTimeout = null;
  let selectedIndex = -1;

  // ✅ anti-duplicados de submit (scanner + Enter)
  let lastSubmitValue = '';
  let lastSubmitAt = 0;

  // Crédito cache
  let creditoInfoCache = {
    clienteId: 0,
    adeudo: 0,
    tieneVencidos: false,
    proximoVenc: null,
    diasRestantes: null
  };
  let creditoPanelOpen = false;

  input.focus();

  // =========================
  // UTIL
  // =========================
  const money = (n) => (Number(n || 0)).toFixed(2);

  const fmtQty = (n) => {
    const x = Number(n || 0);
    if (!Number.isFinite(x)) return '0';
    // muestra hasta 3 decimales sin ceros al final (1 -> "1", 0.35 -> "0.35")
    let s = x.toFixed(3);
    s = s.replace(/\.?0+$/,'');
    return s;
  };

  const numVal = (el) => {
    const v = (el?.value ?? '');
    const n = parseFloat(String(v).replace(',', '.'));
    return Number.isFinite(n) ? n : 0;
  };

  const numText = (el) => {
    const v = (el?.innerText ?? '');
    const n = parseFloat(String(v).replace(',', '.'));
    return Number.isFinite(n) ? n : 0;
  };

  const parseQty = (v) => {
    const n = parseFloat(String(v ?? '').trim().replace(',', '.'));
    return Number.isFinite(n) ? n : NaN;
  };

  function calcTotal() {
    let total = 0;
    detalle.forEach(i => total += (i.cantidad * i.precio));
    return Number(total.toFixed(2));
  }

  function clearSelected() {
    selectedIndex = -1;
    tbody.querySelectorAll('tr').forEach(tr => tr.classList.remove('selected'));
  }

  function setSelected(idx) {
    clearSelected();
    if (idx < 0 || idx >= detalle.length) return;
    selectedIndex = idx;
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const tr = rows[idx];
    if (tr) tr.classList.add('selected');
  }

  function pad2(n) { return String(n).padStart(2, '0'); }

  function fmtDateTime(d) {
    const yyyy = d.getFullYear();
    const mm = pad2(d.getMonth() + 1);
    const dd = pad2(d.getDate());
    const hh = pad2(d.getHours());
    const mi = pad2(d.getMinutes());
    const ss = pad2(d.getSeconds());
    return `${yyyy}-${mm}-${dd} ${hh}:${mi}:${ss}`;
  }

  function uuid() {
    return 'ES-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 8);
  }

  function isOpen(el) {
    return !!el && el.style.display !== 'none' && el.style.display !== '';
  }

  // ✅ Detectores para decidir auto-submit (scanner) vs manual (tecleo por nombre)
  function isDigitsOnly(str) {
    const s = String(str || '').trim();
    return s.length > 0 && /^[0-9]+$/.test(s);
  }

  function hasLetter(str) {
    const s = String(str || '');
    return /[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]/.test(s);
  }

  function submitCodigoDesdeInput(valor, reason = 'unknown') {
    const codigo = String(valor || '').trim();
    if (!codigo) return;

    const now = Date.now();
    // evita doble submit si scanner manda Enter y además cae el timeout
    if (codigo === lastSubmitValue && (now - lastSubmitAt) < 350) {
      return;
    }
    lastSubmitValue = codigo;
    lastSubmitAt = now;

    if (scanTimeout) { clearTimeout(scanTimeout); scanTimeout = null; }

    input.value = '';
    buscarArticulo(codigo);
  }

  // =========================================================
  // ✅ CLICK EN CUALQUIER PARTE = ENFOCAR INPUT ESCÁNER
  // =========================================================
  function isCobroOpen() {
    return !!modalCobro && !modalCobro.classList.contains('hidden');
  }

  function isAnyModalOpen() {
    return isOpen(modalClientes) || isOpen(modalCrearCliente) || isOpen(modalEspera) || isCobroOpen() || isOpen(modalCantidad);
  }

  function shouldIgnoreClickTarget(target) {
    if (!target) return true;
    if (target.closest('input, textarea, select, [contenteditable="true"]')) return true;
    if (target.closest('button, a, .btn, [role="button"]')) return true;
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

  // =========================
  // ✅ MODAL CANTIDAD
  // =========================
  function showQtyError(msg) {
    if (!qtyError) return;
    qtyError.innerText = msg || '';
    qtyError.style.display = msg ? 'block' : 'none';
  }

  function abrirModalCantidad(idx) {
    if (!modalCantidad) return;
    if (idx < 0 || idx >= detalle.length) return;

    editQtyIndex = idx;
    const it = detalle[idx];

    if (qtyArticuloTxt) qtyArticuloTxt.innerText = `${it.codigo} — ${it.descripcion}`;
    if (qtyStockTxt) qtyStockTxt.innerText = fmtQty(it.stock);

    showQtyError('');

    if (qtyCantidadInput) {
      qtyCantidadInput.value = fmtQty(it.cantidad);
      setTimeout(() => {
        qtyCantidadInput.focus();
        qtyCantidadInput.select();
      }, 30);
    }

    modalCantidad.style.display = 'flex';
  }

  function cerrarModalCantidad() {
    if (!modalCantidad) return;
    modalCantidad.style.display = 'none';
    showQtyError('');
    editQtyIndex = -1;
    input.focus();
  }

  function ajustarCantidad(delta) {
    if (editQtyIndex < 0 || editQtyIndex >= detalle.length) return;
    const it = detalle[editQtyIndex];
    const cur = parseQty(qtyCantidadInput?.value);
    const base = Number.isFinite(cur) ? cur : Number(it.cantidad || 0);
    let next = base + delta;

    // redondeo suave para evitar 0.30000000004
    next = Math.round(next * 1000) / 1000;

    if (next < 0) next = 0;

    if (qtyCantidadInput) {
      qtyCantidadInput.value = fmtQty(next);
      qtyCantidadInput.focus();
      qtyCantidadInput.select();
    }
  }

  function guardarCantidad() {
    if (editQtyIndex < 0 || editQtyIndex >= detalle.length) return;
    const it = detalle[editQtyIndex];

    const v = parseQty(qtyCantidadInput?.value);
    if (!Number.isFinite(v)) {
      showQtyError('Cantidad inválida.');
      qtyCantidadInput && qtyCantidadInput.focus();
      return;
    }

    if (v <= 0) {
      showQtyError('La cantidad debe ser mayor a 0.');
      qtyCantidadInput && qtyCantidadInput.focus();
      return;
    }

    const stock = Number(it.stock || 0);
    if (v > stock) {
      showQtyError(`Stock insuficiente. Máximo: ${fmtQty(stock)}`);
      qtyCantidadInput && qtyCantidadInput.focus();
      return;
    }

    it.cantidad = v;

    render();
    setSelected(editQtyIndex);
    cerrarModalCantidad();
  }

  if (btnCerrarCantidad) btnCerrarCantidad.onclick = cerrarModalCantidad;
  if (btnCancelarCantidad) btnCancelarCantidad.onclick = cerrarModalCantidad;
  if (btnGuardarCantidad) btnGuardarCantidad.onclick = guardarCantidad;


  if (btnQtyMenos) btnQtyMenos.onclick = () => ajustarCantidad(-1);
  if (btnQtyMas) btnQtyMas.onclick = () => ajustarCantidad(1);
  if (btnQtyMenos10) btnQtyMenos10.onclick = () => ajustarCantidad(-0.10);
  if (btnQtyMas10) btnQtyMas10.onclick = () => ajustarCantidad(0.10);

  if (qtyCantidadInput) {
    qtyCantidadInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        guardarCantidad();
      } else if (e.key === 'Escape') {
        e.preventDefault();
        cerrarModalCantidad();
      }
    });
  }

  // =========================
  // MODAL CLIENTES
  // =========================
  function filtrarClientes(texto) {
    const t = (texto || '').toLowerCase();
    if (!tablaClientesBody) return;

    tablaClientesBody.querySelectorAll('tr').forEach(tr => {
      if (!tr.dataset.id) return;
      const txt = (tr.dataset.text || tr.innerText || '').toLowerCase();
      tr.style.display = txt.includes(t) ? '' : 'none';
    });
  }

  function abrirModalClientes() {
    if (!modalClientes) return;
    modalClientes.style.display = 'flex';
    clienteFilaSel = null;

    setTimeout(() => {
      if (buscarCliente) {
        buscarCliente.value = '';
        buscarCliente.focus();
      }
      filtrarClientes('');
    }, 30);
  }

  function cerrarModalClientes() {
    if (!modalClientes) return;
    modalClientes.style.display = 'none';
    clienteFilaSel = null;
    input.focus();
  }

  function seleccionarFilaCliente(tr) {
    tablaClientesBody.querySelectorAll('tr').forEach(r => r.classList.remove('selected'));
    tr.classList.add('selected');
    clienteFilaSel = tr;
  }

  function aplicarClienteSeleccionado() {
    if (!clienteFilaSel) {
      alert('Selecciona un cliente');
      return;
    }

    clienteId.value = clienteFilaSel.dataset.id;
    clienteNombre.innerText = clienteFilaSel.dataset.text || '';
    clienteCodigo.innerText = clienteFilaSel.dataset.codigo || '';

    clienteId.dataset.limite  = clienteFilaSel.dataset.limite  || '0';
    clienteId.dataset.dias    = clienteFilaSel.dataset.dias    || '0';
    clienteId.dataset.permite = clienteFilaSel.dataset.permite || '0';

    if (creditoPanelOpen) {
      creditoInfoCache = {
        clienteId: 0,
        adeudo: 0,
        tieneVencidos: false,
        proximoVenc: null,
        diasRestantes: null
      };
      actualizarCreditoUI();
    }

    cerrarModalClientes();
  }

  if (btnCliente) btnCliente.onclick = abrirModalClientes;
  if (btnCerrarClientes) btnCerrarClientes.onclick = cerrarModalClientes;
  if (btnCancelarClientes) btnCancelarClientes.onclick = cerrarModalClientes;
  if (btnSeleccionarCliente) btnSeleccionarCliente.onclick = aplicarClienteSeleccionado;

  if (modalClientes) {
    modalClientes.addEventListener('click', (e) => {
      if (e.target === modalClientes) cerrarModalClientes();
    });
  }

  if (tablaClientesBody) {
    tablaClientesBody.addEventListener('click', (e) => {
      const tr = e.target.closest('tr[data-id]');
      if (!tr) return;
      seleccionarFilaCliente(tr);
    });

    tablaClientesBody.addEventListener('dblclick', (e) => {
      const tr = e.target.closest('tr[data-id]');
      if (!tr) return;
      seleccionarFilaCliente(tr);
      aplicarClienteSeleccionado();
    });
  }

  if (buscarCliente) buscarCliente.oninput = () => filtrarClientes(buscarCliente.value);

  // =========================
  // MODAL CREAR CLIENTE (POS)
  // =========================
  function abrirModalCrearCliente() {
    if (!modalCrearCliente) return;

    modalCrearCliente.style.display = 'flex';
    creandoCliente = false;

    if (cc_error) {
      cc_error.style.display = 'none';
      cc_error.innerText = '';
    }

    if (cc_nombre) cc_nombre.value = '';
    if (cc_telefono) cc_telefono.value = '';
    if (cc_email) cc_email.value = '';
    if (cc_direccion) cc_direccion.value = '';
    if (cc_limite) cc_limite.value = '0';
    if (cc_dias) cc_dias.value = '0';
    if (cc_permite) cc_permite.checked = true;

    setTimeout(() => cc_nombre && cc_nombre.focus(), 30);
  }

  function cerrarModalCrearCliente() {
    if (!modalCrearCliente) return;
    modalCrearCliente.style.display = 'none';
    creandoCliente = false;
    setTimeout(() => buscarCliente && buscarCliente.focus(), 30);
  }

  if (btnCrearCliente) btnCrearCliente.onclick = abrirModalCrearCliente;
  if (btnCerrarCrearCliente) btnCerrarCrearCliente.onclick = cerrarModalCrearCliente;
  if (btnCancelarCrearCliente) btnCancelarCrearCliente.onclick = cerrarModalCrearCliente;

  if (modalCrearCliente) {
    modalCrearCliente.addEventListener('click', (e) => {
      if (e.target === modalCrearCliente) cerrarModalCrearCliente();
    });
  }

  async function guardarClienteRapido() {
    if (creandoCliente) return;
    creandoCliente = true;

    const nombre = (cc_nombre?.value || '').trim();
    if (!nombre) {
      if (cc_error) {
        cc_error.innerText = 'El nombre es obligatorio';
        cc_error.style.display = 'block';
      }
      cc_nombre && cc_nombre.focus();
      creandoCliente = false;
      return;
    }

    const payload = {
      nombre,
      telefono: (cc_telefono?.value || '').trim() || null,
      email: (cc_email?.value || '').trim() || null,
      direccion: (cc_direccion?.value || '').trim() || null,
      permite_credito: cc_permite?.checked ? 1 : 0,
      limite_credito: Number(cc_limite?.value || 0),
      dias_credito: Number(cc_dias?.value || 0)
    };

    try {
      const r = await fetch('/operaciones/clientes/crearRapido', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      let res = null;
      try { res = await r.json(); } catch {}

      if (!r.ok || !res || res.ok !== true || !res.cliente) {
        const msg = (res && res.msg) ? res.msg : `No se pudo crear el cliente (${r.status})`;
        if (cc_error) {
          cc_error.innerText = msg;
          cc_error.style.display = 'block';
        }
        creandoCliente = false;
        return;
      }

      const c = res.cliente;

      if (tablaClientesBody) {
        const tr = document.createElement('tr');
        tr.dataset.id = c.id;
        tr.dataset.text = c.nombre || '';
        tr.dataset.codigo = c.codigo || '';
        tr.dataset.limite = String(c.limite_credito ?? 0);
        tr.dataset.dias = String(c.dias_credito ?? 0);
        tr.dataset.permite = String(c.permite_credito ?? 0);

        tr.innerHTML = `
          <td>•</td>
          <td>${c.nombre || ''}</td>
          <td>${c.codigo || ''}</td>
          <td style="text-align:right;">${Number(c.limite_credito || 0).toFixed(2)}</td>
        `;

        tr.addEventListener('click', () => {
          tablaClientesBody.querySelectorAll('tr').forEach(r => r.classList.remove('selected'));
          tr.classList.add('selected');
          clienteFilaSel = tr;
        });

        tr.addEventListener('dblclick', () => {
          tablaClientesBody.querySelectorAll('tr').forEach(r => r.classList.remove('selected'));
          tr.classList.add('selected');
          clienteFilaSel = tr;
          aplicarClienteSeleccionado();
        });

        tablaClientesBody.prepend(tr);
        clienteFilaSel = tr;
      }

      aplicarClienteSeleccionado();
      cerrarModalCrearCliente();

    } catch (err) {
      if (cc_error) {
        cc_error.innerText = 'Error creando cliente';
        cc_error.style.display = 'block';
      }
    } finally {
      creandoCliente = false;
    }
  }

  if (btnGuardarClienteRapido) btnGuardarClienteRapido.onclick = guardarClienteRapido;

  // =========================
  // ESCÁNER / INPUT
  // =========================
  input.addEventListener('input', () => {
    if (scanTimeout) { clearTimeout(scanTimeout); scanTimeout = null; }

    const raw = input.value;
    const v = String(raw || '').trim();
    if (!v) return;

    const modoScanner = isDigitsOnly(v) && !hasLetter(v);

    if (!modoScanner) {
      return; // modo manual: espera Enter
    }

    scanTimeout = setTimeout(() => {
      const codigo = String(input.value || '').trim();
      if (!codigo) return;
      if (!isDigitsOnly(codigo) || hasLetter(codigo)) return;
      submitCodigoDesdeInput(codigo, 'scanner-timeout');
    }, 250);
  });

  input.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;

    const v = String(input.value || '').trim();
    if (!v) return;

    if (!isDigitsOnly(v) || hasLetter(v)) {
      e.preventDefault();
      submitCodigoDesdeInput(v, 'manual-enter');
      return;
    }

    e.preventDefault();
    submitCodigoDesdeInput(v, 'digits-enter');
  });

  // =========================
  // BUSCAR ARTÍCULO
  // =========================
  function buscarArticulo(codigo) {
    fetch(`/operaciones/articulos/buscar?codigo=${encodeURIComponent(codigo)}`)
      .then(r => r.json())
      .then(articulo => {
        if (!articulo) {
          alert('Artículo no encontrado');
          input.focus();
          return;
        }

        const stock = Number(articulo.stock || 0);
        if (stock <= 0) {
          alert('Sin stock disponible');
          return;
        }

        agregarLinea(articulo);
      })
      .catch(() => alert('Error buscando artículo'));
  }

  // =========================
  // AGREGAR / UPDATE LINEA
  // =========================
  function agregarLinea(articulo) {
    const stock  = Number(articulo.stock || 0);
    const precio = Number(articulo.precio_venta || 0);

    const item = detalle.find(i => i.articulo_id === articulo.id);

    if (item) {
      if (item.cantidad + 1 > stock) {
        alert('Stock insuficiente');
        return;
      }
      item.cantidad += 1;
    } else {
      detalle.push({
        articulo_id: articulo.id,
        codigo: articulo.codigo,
        descripcion: articulo.descripcion,
        cantidad: 1,
        precio,
        stock
      });
    }

    render();
  }

  // =========================
  // REMOVER
  // =========================
  function removerSeleccionado() {
    if (selectedIndex < 0 || selectedIndex >= detalle.length) {
      alert('Selecciona un artículo para remover');
      return;
    }
    detalle.splice(selectedIndex, 1);
    clearSelected();
    render();
  }

  if (btnRemover) btnRemover.onclick = removerSeleccionado;

  // =========================
  // RENDER
  // =========================
  function render() {
    tbody.innerHTML = '';
    const total = calcTotal();

    detalle.forEach((item, idx) => {
      const importe = item.cantidad * item.precio;
      const tr = document.createElement('tr');

      tr.innerHTML = `
        <td>${item.codigo}</td>
        <td>${item.descripcion}</td>
        <td style="text-align:right">${fmtQty(item.stock)}</td>
        <td style="text-align:right">${fmtQty(item.cantidad)}</td>
        <td style="text-align:right">${money(item.precio)}</td>
        <td style="text-align:right">${money(importe)}</td>
      `;

      tr.addEventListener('click', () => {
        setSelected(idx);
      });

      tr.addEventListener('dblclick', () => {
        setSelected(idx);
        abrirModalCantidad(idx);
      });

      tbody.appendChild(tr);
    });

    totalSpan.innerText = money(total);

    // si había selección y sigue existiendo, re-aplica
    if (selectedIndex >= 0 && selectedIndex < detalle.length) {
      setSelected(selectedIndex);
    }
  }

  // =========================================================
  // ESPERA (localStorage)
  // =========================================================
  const LS_KEY_ESPERA = 'ventas_en_espera';

  function getEsperaList() {
    try {
      const raw = localStorage.getItem(LS_KEY_ESPERA);
      const arr = raw ? JSON.parse(raw) : [];
      return Array.isArray(arr) ? arr : [];
    } catch {
      return [];
    }
  }

  function saveEsperaList(arr) {
    localStorage.setItem(LS_KEY_ESPERA, JSON.stringify(arr));
  }

  function guardarEnEspera() {
    if (!detalle.length) {
      alert('No hay artículos para poner en espera');
      return;
    }

    const total = calcTotal();
    const now = new Date();

    const item = {
      id: uuid(),
      fecha: now.toISOString(),
      fecha_txt: fmtDateTime(now),
      cliente: {
        id: clienteId?.value || null,
        nombre: clienteNombre?.innerText || '',
        codigo: clienteCodigo?.innerText || '',
        limite: clienteId?.dataset?.limite || '0',
        dias: clienteId?.dataset?.dias || '0',
        permite: clienteId?.dataset?.permite || '0'
      },
      total,
      detalle
    };

    const list = getEsperaList();
    list.unshift(item);
    saveEsperaList(list);

    detalle = [];
    clearSelected();
    render();

    alert('Venta enviada a Espera');
    input.focus();
  }

  function renderTablaEspera(list) {
    if (!tablaEsperaBody) return;

    tablaEsperaBody.innerHTML = '';

    if (!list.length) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td class="prov-empty" colspan="4">No hay ventas en espera</td>`;
      tablaEsperaBody.appendChild(tr);
      return;
    }

    list.forEach(v => {
      const tr = document.createElement('tr');
      tr.dataset.id = v.id;
      tr.dataset.text = `${v.fecha_txt} ${v?.cliente?.nombre || ''} ${money(v.total)}`.toLowerCase();

      tr.innerHTML = `
        <td>${v.fecha_txt || ''}</td>
        <td>Ticket</td>
        <td>${v?.cliente?.nombre || 'Público en General'}</td>
        <td style="text-align:right; font-variant-numeric: tabular-nums;">${money(v.total)}</td>
      `;

      tr.addEventListener('click', () => {
        tablaEsperaBody.querySelectorAll('tr').forEach(r => r.classList.remove('selected'));
        tr.classList.add('selected');
        esperaFilaSelId = v.id;
      });

      tr.addEventListener('dblclick', () => {
        tablaEsperaBody.querySelectorAll('tr').forEach(r => r.classList.remove('selected'));
        tr.classList.add('selected');
        esperaFilaSelId = v.id;
        aplicarRecuperarEspera();
      });

      tablaEsperaBody.appendChild(tr);
    });
  }

  function abrirModalEspera() {
    const list = getEsperaList();
    if (!list.length) {
      alert('No hay ventas en espera');
      return;
    }

    if (!modalEspera) return;

    modalEspera.style.display = 'flex';
    esperaFilaSelId = null;

    renderTablaEspera(list);

    setTimeout(() => {
      if (buscarEspera) {
        buscarEspera.value = '';
        buscarEspera.focus();
      }
    }, 30);
  }

  function cerrarModalEspera() {
    if (!modalEspera) return;
    modalEspera.style.display = 'none';
    esperaFilaSelId = null;
    input.focus();
  }

  function filtrarEspera(texto) {
    const t = (texto || '').toLowerCase();
    tablaEsperaBody?.querySelectorAll('tr[data-id]').forEach(tr => {
      const txt = (tr.dataset.text || tr.innerText || '').toLowerCase();
      tr.style.display = txt.includes(t) ? '' : 'none';
    });
  }

  if (buscarEspera) buscarEspera.oninput = () => filtrarEspera(buscarEspera.value);

  function aplicarRecuperarEspera() {
    if (!esperaFilaSelId) {
      alert('Selecciona una venta en espera');
      return;
    }

    const list = getEsperaList();
    const idx = list.findIndex(x => x.id === esperaFilaSelId);

    if (idx < 0) {
      alert('Esa venta ya no existe en espera');
      cerrarModalEspera();
      return;
    }

    const v = list[idx];

    if (v?.cliente) {
      if (clienteId) {
        clienteId.value = v.cliente.id || '';
        clienteId.dataset.limite  = v.cliente.limite  || '0';
        clienteId.dataset.dias    = v.cliente.dias    || '0';
        clienteId.dataset.permite = v.cliente.permite || '0';
      }
      if (clienteNombre) clienteNombre.innerText = v.cliente.nombre || 'Público en General';
      if (clienteCodigo) clienteCodigo.innerText = v.cliente.codigo || '';
    }

    detalle = Array.isArray(v.detalle) ? v.detalle : [];
    clearSelected();
    render();

    list.splice(idx, 1);
    saveEsperaList(list);

    cerrarModalEspera();
    alert('Venta recuperada de Espera');
    input.focus();
  }

  if (btnEspera) btnEspera.onclick = guardarEnEspera;
  if (btnRecuperar) btnRecuperar.onclick = abrirModalEspera;

  if (btnCerrarEspera) btnCerrarEspera.onclick = cerrarModalEspera;
  if (btnCancelarEspera) btnCancelarEspera.onclick = cerrarModalEspera;
  if (btnAceptarEspera) btnAceptarEspera.onclick = aplicarRecuperarEspera;

  if (modalEspera) {
    modalEspera.addEventListener('click', (e) => {
      if (e.target === modalEspera) cerrarModalEspera();
    });
  }

  // =========================
  // TOOLBAR
  // =========================
  if (btnCancelar) btnCancelar.onclick = () => location.reload();
  if (btnCobrar) btnCobrar.onclick = abrirModalCobro;

  // =========================
  // COBRO (MIXTO + CRÉDITO)
  // =========================
  function addDaysToDate(date, days) {
    const d = new Date(date.getTime());
    d.setDate(d.getDate() + Number(days || 0));
    return d;
  }

  function fmtDate(d) {
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  }

  function hideCreditoPanel() {
    if (!creditoPanel) return;
    creditoPanel.classList.add('hidden');
    creditoPanelOpen = false;
    if (creditoWarning) creditoWarning.style.display = 'none';
  }

  function showCreditoPanel() {
    if (!creditoPanel) return;
    creditoPanel.classList.remove('hidden');
    creditoPanelOpen = true;
  }

  function getTotalCobro() {
    return Number(numText(totalCobroSpan).toFixed(2));
  }

  function getPagoTotal() {
    const p = numVal(efectivoInput) + numVal(tarjetaInput) + numVal(transferenciaInput);
    return Number(p.toFixed(2));
  }

  function getRestanteCredito() {
    const total = getTotalCobro();
    const pagado = getPagoTotal();
    const restante = total - pagado;
    return Number(Math.max(0, restante).toFixed(2));
  }

  function actualizarCambioYCreditoTotal() {
    const total = getTotalCobro();
    const pagado = getPagoTotal();
    const cambio = Number((pagado - total).toFixed(2));

    if (cambioCobroSpan) cambioCobroSpan.innerText = money(cambio);

    const restante = getRestanteCredito();
    if (creditoTotal) creditoTotal.value = money(restante);

    if (creditoPanelOpen) actualizarCreditoUI();
  }

  async function cargarCreditoInfo(cliente_id) {
    try {
      const r = await fetch(`/operaciones/ventas/creditoInfo?cliente_id=${encodeURIComponent(cliente_id)}`);
      const info = await r.json();
      creditoInfoCache = {
        clienteId: cliente_id,
        adeudo: Number(info?.adeudo || 0),
        tieneVencidos: !!info?.tieneVencidos,
        proximoVenc: info?.proximoVenc || null,
        diasRestantes: (typeof info?.diasRestantes === 'number') ? info.diasRestantes : null
      };
    } catch {
      creditoInfoCache = {
        clienteId: cliente_id,
        adeudo: 0,
        tieneVencidos: false,
        proximoVenc: null,
        diasRestantes: null
      };
    }
  }

  function actualizarCreditoUI() {
    const totalCredito = getRestanteCredito();

    const diasCfg = Number(clienteId?.dataset?.dias || 0);
    const limite  = Number(clienteId?.dataset?.limite || 0);
    const permite = Number(clienteId?.dataset?.permite || 0);

    const cid = Number(clienteId?.value || 0);
    const cacheOk = (creditoInfoCache.clienteId === cid);

    if (creditoDias) {
      if (cacheOk && typeof creditoInfoCache.diasRestantes === 'number') {
        creditoDias.value = creditoInfoCache.diasRestantes;
      } else {
        creditoDias.value = diasCfg;
      }
    }

    if (creditoLimite) creditoLimite.value = money(limite);

    if (creditoVence) {
      if (cacheOk && creditoInfoCache.proximoVenc) {
        creditoVence.value = creditoInfoCache.proximoVenc;
      } else {
        creditoVence.value = diasCfg > 0 ? fmtDate(addDaysToDate(new Date(), diasCfg)) : '-';
      }
    }

    if (!cid) {
      if (creditoAdeudo) creditoAdeudo.value = '0.00';
      if (creditoDisponible) creditoDisponible.value = money(limite);
      if (creditoWarning) creditoWarning.style.display = 'none';
      return;
    }

    const adeudo = cacheOk ? Number(creditoInfoCache.adeudo || 0) : 0;
    const tieneVencidos = cacheOk ? !!creditoInfoCache.tieneVencidos : false;

    const disponible = Math.max(0, limite - adeudo);
    if (creditoAdeudo) creditoAdeudo.value = money(adeudo);
    if (creditoDisponible) creditoDisponible.value = money(disponible);

    if (!creditoWarning) return;

    if (totalCredito <= 0) {
      creditoWarning.style.display = 'none';
      return;
    }

    if (permite !== 1) {
      creditoWarning.innerText = '⚠ Este cliente NO tiene permitido crédito.';
      creditoWarning.style.display = 'block';
    } else if (tieneVencidos) {
      creditoWarning.innerText = '⚠ Cliente con deuda vencida: no se permite fiado.';
      creditoWarning.style.display = 'block';
    } else if ((adeudo + totalCredito) > limite && limite > 0) {
      creditoWarning.innerText = '⚠ Crédito insuficiente: excede el límite del cliente.';
      creditoWarning.style.display = 'block';
    } else {
      creditoWarning.style.display = 'none';
    }
  }

  async function abrirCreditoPanel() {
    const cid = Number(clienteId?.value || 0);
    if (!cid) {
      alert('Selecciona un cliente para fiado (F2)');
      abrirModalClientes();
      return;
    }

    showCreditoPanel();

    if (creditoInfoCache.clienteId !== cid) {
      await cargarCreditoInfo(cid);
    }

    actualizarCambioYCreditoTotal();
    actualizarCreditoUI();
  }

  function cerrarCreditoPanel() {
    hideCreditoPanel();
  }

  function abrirModalCobro() {
    if (!detalle.length) {
      alert('No hay artículos en la venta');
      return;
    }

    if (totalCobroSpan) totalCobroSpan.innerText = totalSpan.innerText;

    if (modalCobro) modalCobro.classList.remove('hidden');

    if (efectivoInput) efectivoInput.value = totalSpan.innerText;
    if (tarjetaInput) tarjetaInput.value = 0;
    if (transferenciaInput) transferenciaInput.value = 0;
    if (referenciaInput) referenciaInput.value = '';

    cerrarCreditoPanel();
    actualizarCambioYCreditoTotal();

    efectivoInput && efectivoInput.focus();
  }

  if (btnToggleCredito) {
    btnToggleCredito.addEventListener('click', async () => {
      if (creditoPanelOpen) {
        cerrarCreditoPanel();
      } else {
        await abrirCreditoPanel();
      }
    });
  }

  const cerrarCobroBtn = document.getElementById('cerrarCobro');
  if (cerrarCobroBtn) {
    cerrarCobroBtn.onclick = () => {
      modalCobro && modalCobro.classList.add('hidden');
      cerrarCreditoPanel();
      input.focus();
    };
  }

  [efectivoInput, tarjetaInput, transferenciaInput].forEach(i => {
    if (!i) return;
    i.addEventListener('input', () => {
      actualizarCambioYCreditoTotal();
    });
  });

  // =========================
  // CONFIRMAR VENTA
  // =========================
  const confirmarCobroBtn = document.getElementById('confirmarCobro');
  if (confirmarCobroBtn) {
    confirmarCobroBtn.onclick = async () => {
      const total = getTotalCobro();
      const pagado = getPagoTotal();
      const restanteCredito = getRestanteCredito();

      if (restanteCredito > 0) {
        const cid = Number(clienteId?.value || 0);
        if (!cid) {
          alert('Selecciona un cliente para fiado (F2)');
          abrirModalClientes();
          return;
        }

        if (creditoInfoCache.clienteId !== cid) {
          await cargarCreditoInfo(cid);
        }

        actualizarCreditoUI();

        if (creditoWarning && creditoWarning.style.display !== 'none' && creditoWarning.innerText.trim() !== '') {
          alert(creditoWarning.innerText.replace('⚠', '').trim());
          return;
        }
      } else {
        if (pagado < total) {
          alert('Pago insuficiente');
          return;
        }
      }

      let tipo_pago = 'CONTADO';
      if (restanteCredito > 0 && pagado > 0) tipo_pago = 'MIXTO';
      else if (restanteCredito > 0 && pagado === 0) tipo_pago = 'CREDITO';

      fetch('/operaciones/ventas/guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          venta: {
            cliente_id: clienteId?.value || null,
            total: total,
            efectivo: numVal(efectivoInput),
            tarjeta: numVal(tarjetaInput),
            transferencia: numVal(transferenciaInput),
            referencia: referenciaInput?.value || null,
            tipo_pago: tipo_pago
          },
          detalle
        })
      })
        .then(r => r.json())
        .then(res => {
          if (!res || res.ok !== true) {
            alert(res?.msg || 'Error al guardar la venta');
            return;
          }
          location.reload();
        })
        .catch(() => alert('Error al guardar la venta'));
    };
  }

  // =========================
  // TECLAS TIPO POS
  // =========================
  document.addEventListener('keydown', (e) => {

    // ✅ Si está abierto el modal de cantidad, Escape/Enter se manejan aquí también
    if (isOpen(modalCantidad)) {
      if (e.key === 'Escape') {
        e.preventDefault();
        cerrarModalCantidad();
        return;
      }
      if (e.key === 'Enter') {
        // si el focus no está en input, igual guardamos
        if (document.activeElement !== qtyCantidadInput) {
          e.preventDefault();
          guardarCantidad();
          return;
        }
      }
      return;
    }

    if (isOpen(modalCrearCliente)) {
      if (e.key === 'Escape') {
        e.preventDefault();
        cerrarModalCrearCliente();
        return;
      }
      if (e.key === 'Enter') {
        e.preventDefault();
        guardarClienteRapido();
        return;
      }
      return;
    }

    // ✅ F4 = editar cantidad del seleccionado
    if (e.key === 'F4') {
      e.preventDefault();
      if (selectedIndex < 0 || selectedIndex >= detalle.length) {
        alert('Selecciona un artículo para editar cantidad');
        return;
      }
      abrirModalCantidad(selectedIndex);
      return;
    }

    // ✅ Escape general: solo si NO hay otros modales abiertos
    if (e.key === 'Escape') {
      if (isAnyModalOpen()) {
        // si hay un modal abierto diferente, no recargamos
        return;
      }
      e.preventDefault();
      location.reload();
      return;
    }

    if (e.key === 'F2') {
      e.preventDefault();
      abrirModalClientes();
      return;
    }

    if (e.key === 'F3') {
      e.preventDefault();

      if (modalCobro && !modalCobro.classList.contains('hidden')) {
        confirmarCobroBtn && confirmarCobroBtn.click();
        return;
      }

      abrirModalCobro();
      return;
    }

    if (e.key === 'F6') {
      e.preventDefault();
      removerSeleccionado();
      return;
    }

    if (e.key === 'F7') {
      e.preventDefault();
      guardarEnEspera();
      return;
    }

    if (e.key === 'F9') {
      e.preventDefault();
      abrirModalEspera();
      return;
    }

    if (isOpen(modalClientes) && e.key === 'Enter') {
      e.preventDefault();
      aplicarClienteSeleccionado();
      return;
    }

    if (isOpen(modalEspera) && e.key === 'Enter') {
      e.preventDefault();
      aplicarRecuperarEspera();
      return;
    }
  });

});
