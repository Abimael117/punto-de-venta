document.addEventListener('DOMContentLoaded', () => {

  const fechaCorte       = document.getElementById('fechaCorte');
  const horaCorte        = document.getElementById('horaCorte');
  const efectivoContado  = document.getElementById('efectivoContado');
  const notasCorte       = document.getElementById('notasCorte');

  const kpiTotal         = document.getElementById('kpiTotal');
  const kpiEfectivo      = document.getElementById('kpiEfectivo');
  const kpiTarjeta       = document.getElementById('kpiTarjeta');
  const kpiTransferencia = document.getElementById('kpiTransferencia');
  const kpiCredito       = document.getElementById('kpiCredito');

  const esperadoEfectivo = document.getElementById('esperadoEfectivo');
  const difMonto         = document.getElementById('difMonto');

  // Cálculo caja (solo visual)
  const ccDineroInicial  = document.getElementById('ccDineroInicial');
  const ccComprasCaja    = document.getElementById('ccComprasCaja');
  const ccCajaCalc       = document.getElementById('ccCajaCalc');

  const btnGuardar        = document.getElementById('btnGuardar');
  const btnCancelar       = document.getElementById('btnCancelar');
  const btnHistorial      = document.getElementById('btnHistorial');

  // Modal historial
  const modalHistorial        = document.getElementById('modalHistorial');
  const btnCerrarHistorial    = document.getElementById('btnCerrarHistorial');
  const btnCerrarHistorial2   = document.getElementById('btnCerrarHistorial2');
  const btnRecargarHistorial  = document.getElementById('btnRecargarHistorial');
  const buscarHistorial       = document.getElementById('buscarHistorial');
  const tablaHistorialBody    = document.querySelector('#tablaHistorial tbody');

  if (!fechaCorte || !efectivoContado || !kpiTotal || !kpiEfectivo || !kpiTarjeta || !kpiTransferencia || !kpiCredito || !esperadoEfectivo || !difMonto) {
    console.warn('CorteCaja: faltan elementos en el DOM');
    return;
  }

  // ✅ ahora el resumen incluye compras_caja
  let resumen = { total: 0, efectivo: 0, tarjeta: 0, transferencia: 0, credito: 0, compras_caja: 0 };

  const money = (n) => Number(n || 0).toFixed(2);

  function todayYMD() {
    const d = new Date();
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  }

  function nowHM() {
    const d = new Date();
    const hh = String(d.getHours()).padStart(2, '0');
    const mm = String(d.getMinutes()).padStart(2, '0');
    return `${hh}:${mm}`;
  }

  async function safeJson(r) {
    try { return await r.json(); } catch { return null; }
  }

  function numFromInput(el) {
    if (!el) return 0;
    return parseFloat((el.value || '0').toString().replace(',', '.')) || 0;
  }

  function calcCaja() {
    const inicial = numFromInput(ccDineroInicial);
    const compras = numFromInput(ccComprasCaja);

    const caja = Number((inicial + Number(resumen.efectivo || 0) - compras).toFixed(2));
    if (ccCajaCalc) ccCajaCalc.innerText = `$${money(caja)}`;
    return caja;
  }

  function calcDiferencia() {
    const contado = numFromInput(efectivoContado);
    const cajaCalc = calcCaja();

    const dif = Number((contado - cajaCalc).toFixed(2));

    difMonto.innerText = `$${money(dif)}`;
    if (Math.abs(dif) < 0.01) {
      difMonto.style.color = '#15803d'; // verde (0)
    } else if (dif > 0) {
      difMonto.style.color = '#b45309'; // amarillo/ámbar (sobrante)
    } else {
      difMonto.style.color = '#b91c1c'; // rojo (faltante)
    }

  }

  function setResumenUI() {
    kpiTotal.innerText         = `$${money(resumen.total)}`;
    kpiEfectivo.innerText      = `$${money(resumen.efectivo)}`;
    kpiTarjeta.innerText       = `$${money(resumen.tarjeta)}`;
    kpiTransferencia.innerText = `$${money(resumen.transferencia)}`;
    kpiCredito.innerText       = `$${money(resumen.credito)}`;

    esperadoEfectivo.innerText = money(resumen.efectivo);

    // Default contado = efectivo sistema
    if ((efectivoContado.value ?? '').trim() === '') {
      efectivoContado.value = money(resumen.efectivo);
    }

    // Defaults
    if (ccDineroInicial && (ccDineroInicial.value ?? '').trim() === '') ccDineroInicial.value = '0.00';

    // ✅ AUTO: compras pagadas con caja del día
    if (ccComprasCaja && (ccComprasCaja.value ?? '').trim() === '') {
      ccComprasCaja.value = money(resumen.compras_caja || 0);
    }

    calcDiferencia();
  }

  async function cargarResumen() {
    const fecha = (fechaCorte.value || todayYMD()).trim();

    try {
      const r = await fetch(`/operaciones/corte-caja/resumen?fecha=${encodeURIComponent(fecha)}`);
      const j = await safeJson(r);

      if (!r.ok || !j || j.ok !== true) {
        console.error('Resumen fallo:', { status: r.status, body: j });
        alert(j?.msg || 'No se pudo cargar el resumen');
        return;
      }

      resumen = j.data || resumen;
      setResumenUI();
    } catch (err) {
      console.error(err);
      alert('Error cargando resumen');
    }
  }

  async function guardarCorte() {
    const fecha = (fechaCorte.value || todayYMD()).trim();
    const hora  = (horaCorte?.value || nowHM()).trim();
    const contado = numFromInput(efectivoContado);

    const payload = {
      fecha,
      hora,
      efectivo_contado: Number(contado.toFixed(2)),
      notas: (notasCorte?.value || '').trim()
    };

    try {
      const r = await fetch('/operaciones/corte-caja/guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const j = await safeJson(r);

      if (!r.ok || !j || j.ok !== true) {
        alert(j?.msg || `No se pudo guardar el corte (${r.status})`);
        return;
      }

      alert('✅ Corte guardado');
      abrirHistorial();
    } catch (err) {
      console.error(err);
      alert('Error guardando el corte');
    }
  }

  function isModalOpen() {
    return !!(modalHistorial && modalHistorial.style.display === 'flex');
  }

  function abrirHistorial() {
    if (!modalHistorial) return;
    modalHistorial.style.display = 'flex';
    cargarHistorial();
    setTimeout(() => buscarHistorial && buscarHistorial.focus(), 30);
  }

  function cerrarHistorial() {
    if (!modalHistorial) return;
    modalHistorial.style.display = 'none';
    setTimeout(() => efectivoContado && efectivoContado.focus(), 30);
  }

  function renderHistorial(list) {
    if (!tablaHistorialBody) return;

    tablaHistorialBody.innerHTML = '';

    if (!list.length) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td class="prov-empty" colspan="7">No hay cortes en el rango</td>`;
      tablaHistorialBody.appendChild(tr);
      return;
    }

    list.forEach(row => {
      const tr = document.createElement('tr');
      const dif = Number(row.diferencia || 0);
      const hora = (row.hora_corte || '').toString().slice(0,5);

      tr.dataset.text = `${row.fecha} ${hora} ${row.notas || ''} ${row.total_sistema} ${row.efectivo_sistema} ${row.efectivo_contado} ${row.diferencia}`.toLowerCase();

      tr.innerHTML = `
        <td>${row.fecha || ''}</td>
        <td>${hora || ''}</td>
        <td style="text-align:right;">$${money(row.total_sistema)}</td>
        <td style="text-align:right;">$${money(row.efectivo_sistema)}</td>
        <td style="text-align:right;">$${money(row.efectivo_contado)}</td>
        <td style="text-align:right; font-weight:900; color:${Math.abs(dif) < 0.01 ? '#15803d' : '#b91c1c'};">
          $${money(dif)}
        </td>
        <td>${row.notas || ''}</td>
      `;

      tablaHistorialBody.appendChild(tr);
    });
  }

  async function cargarHistorial() {
    const now = new Date();
    const desde = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
    const hasta = todayYMD();

    try {
      const r = await fetch(`/operaciones/corte-caja/listar?desde=${encodeURIComponent(desde)}&hasta=${encodeURIComponent(hasta)}`);
      const j = await safeJson(r);

      if (!r.ok || !j || j.ok !== true) {
        alert(j?.msg || 'No se pudo cargar historial');
        return;
      }

      renderHistorial(j.data || []);
    } catch (err) {
      console.error(err);
      alert('Error cargando historial');
    }
  }

  function filtrarHistorial(txt) {
    const t = (txt || '').toLowerCase();
    tablaHistorialBody?.querySelectorAll('tr').forEach(tr => {
      const x = (tr.dataset.text || tr.innerText || '').toLowerCase();
      tr.style.display = x.includes(t) ? '' : 'none';
    });
  }

  // Eventos
  fechaCorte.addEventListener('change', () => {
    efectivoContado.value = '';
    cargarResumen();
  });

  efectivoContado.addEventListener('input', calcDiferencia);
  if (ccDineroInicial) ccDineroInicial.addEventListener('input', calcDiferencia);
  if (ccComprasCaja)   ccComprasCaja.addEventListener('input', calcDiferencia);

  if (btnGuardar)   btnGuardar.onclick   = guardarCorte;
  if (btnCancelar)  btnCancelar.onclick  = () => location.reload();
  if (btnHistorial) btnHistorial.onclick = abrirHistorial;

  if (btnCerrarHistorial)  btnCerrarHistorial.onclick  = cerrarHistorial;
  if (btnCerrarHistorial2) btnCerrarHistorial2.onclick = cerrarHistorial;
  if (btnRecargarHistorial) btnRecargarHistorial.onclick = cargarHistorial;
  if (buscarHistorial) buscarHistorial.oninput = () => filtrarHistorial(buscarHistorial.value);

  if (modalHistorial) {
    modalHistorial.addEventListener('click', (e) => {
      if (e.target === modalHistorial) cerrarHistorial();
    });
  }

  // Teclas tipo POS
  document.addEventListener('keydown', (e) => {
    if (isModalOpen()) {
      if (e.key === 'Escape') { e.preventDefault(); cerrarHistorial(); }
      return;
    }

    if (e.key === 'Escape') { e.preventDefault(); location.reload(); return; }
    if (e.key === 'F3')     { e.preventDefault(); guardarCorte(); return; }
    if (e.key === 'F9')     { e.preventDefault(); abrirHistorial(); return; }
  });

  // INIT
  if (!fechaCorte.value) fechaCorte.value = todayYMD();
  if (horaCorte && !horaCorte.value) horaCorte.value = nowHM();

  cargarResumen();
  setTimeout(() => efectivoContado && efectivoContado.focus(), 60);

});
