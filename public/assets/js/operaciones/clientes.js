document.addEventListener('DOMContentLoaded', () => {

  const tbody = document.getElementById('clientesBody');
  const btnAgregar = document.getElementById('btnAgregar');
  const btnEditar  = document.getElementById('btnEditar');
  const btnEliminar= document.getElementById('btnEliminar');

  const modal = document.getElementById('modalCliente');
  const mcTitulo = document.getElementById('mcTitulo');
  const mcCerrar = document.getElementById('mcCerrar');
  const mcCancelar = document.getElementById('mcCancelar');

  const form = document.getElementById('formCliente');

  const f = {
    id: document.getElementById('cli_id'),
    nombre: document.getElementById('cli_nombre'),
    telefono: document.getElementById('cli_telefono'),
    email: document.getElementById('cli_email'),
    direccion: document.getElementById('cli_direccion'),
    permite: document.getElementById('cli_perm_credito'),
    limite: document.getElementById('cli_limite'),
    dias: document.getElementById('cli_dias'),
  };

  let clientes = [];
  let selectedId = null;

  function money(n){
    const x = Number(n || 0);
    return x.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function abrirModal(titulo){
    mcTitulo.textContent = titulo;
    modal.style.display = 'flex';
    setTimeout(() => f.nombre.focus(), 50);
  }

  function cerrarModal(){
    modal.style.display = 'none';
  }

  mcCerrar?.addEventListener('click', cerrarModal);
  mcCancelar?.addEventListener('click', cerrarModal);

  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) cerrarModal();
    });
  }

  function render(){
    if (!tbody) return;

    if (!clientes.length) {
      tbody.innerHTML = `<tr><td colspan="5" class="prov-empty">No hay clientes</td></tr>`;
      return;
    }

    tbody.innerHTML = '';
    clientes.forEach(c => {
      const tr = document.createElement('tr');
      tr.dataset.id = c.id;

      const creditoTxt = String(c.permite_credito) === '1' ? 'Sí' : 'No';

      tr.innerHTML = `
        <td>${c.codigo ?? ''}</td>
        <td>${c.nombre ?? ''}</td>
        <td>${c.telefono ?? ''}</td>
        <td>${creditoTxt}</td>
        <td style="text-align:right;">$${money(c.limite_credito)}</td>
      `;

      if (String(c.id) === String(selectedId)) tr.classList.add('selected');

      tr.addEventListener('click', () => {
        selectedId = c.id;
        tbody.querySelectorAll('tr').forEach(r => r.classList.remove('selected'));
        tr.classList.add('selected');
      });

      tbody.appendChild(tr);
    });
  }

  function cargar(){
    fetch('/operaciones/clientes/listar', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(res => {
        if (!res || res.ok !== true) throw new Error(res?.msg || 'Error');
        clientes = res.data || [];
        // seleccionar primero por default
        selectedId = clientes[0]?.id ?? null;
        render();
      })
      .catch(() => {
        tbody.innerHTML = `<tr><td colspan="5" class="prov-empty">Error al cargar</td></tr>`;
      });
  }

  function limpiarForm(){
    form.reset();
    f.id.value = '';
    f.limite.value = 0;
    f.dias.value = 0;
    f.permite.value = '0';
  }

  function getSel(){
    if (!selectedId) return null;
    return clientes.find(x => String(x.id) === String(selectedId)) || null;
  }

  // Agregar
  function onAgregar(){
    limpiarForm();
    abrirModal('Agregar cliente');
  }

  // Editar
  function onEditar(){
    const c = getSel();
    if (!c) return alert('Selecciona un cliente');

    limpiarForm();
    f.id.value = c.id;
    f.nombre.value = c.nombre ?? '';
    f.telefono.value = c.telefono ?? '';
    f.email.value = c.email ?? '';
    f.direccion.value = c.direccion ?? '';
    f.permite.value = String(c.permite_credito ?? '0');
    f.limite.value = c.limite_credito ?? 0;
    f.dias.value = c.dias_credito ?? 0;

    abrirModal('Editar cliente');
  }

  // Eliminar (toggle activo=0)
  function onEliminar(){
    const c = getSel();
    if (!c) return alert('Selecciona un cliente');

    const ok = confirm(`¿Eliminar "${c.nombre}"?`);
    if (!ok) return;

    const fd = new FormData();
    fd.append('id', c.id);

    fetch('/operaciones/clientes/eliminar', {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
      if (!res || res.ok !== true) {
        alert(res?.msg || 'No se pudo eliminar');
        return;
      }
      cargar();
    })
    .catch(() => alert('Error al eliminar'));
  }

  btnAgregar?.addEventListener('click', onAgregar);
  btnEditar?.addEventListener('click', onEditar);
  btnEliminar?.addEventListener('click', onEliminar);

  // Submit form
  form?.addEventListener('submit', (e) => {
    e.preventDefault();

    const fd = new FormData(form);
    const isEdit = !!(f.id.value && String(f.id.value).trim() !== '');

    fetch(isEdit ? '/operaciones/clientes/actualizar' : '/operaciones/clientes/guardar', {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
      if (!res || res.ok !== true) {
        alert(res?.msg || 'Error al guardar');
        return;
      }
      cerrarModal();
      cargar();
    })
    .catch(() => alert('Error al guardar'));
  });

  // Teclas POS
  document.addEventListener('keydown', (e) => {
    const modalOpen = modal && modal.style.display !== 'none';

    if (e.key === 'F3') { e.preventDefault(); if (!modalOpen) onAgregar(); }
    if (e.key === 'F4') { e.preventDefault(); if (!modalOpen) onEditar(); }
    if (e.key === 'F6') { e.preventDefault(); if (!modalOpen) onEliminar(); }

    if (e.key === 'Escape' && modalOpen) { e.preventDefault(); cerrarModal(); }
  });

  cargar();
});
