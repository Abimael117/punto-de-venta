document.addEventListener('DOMContentLoaded', () => {

    let clienteSeleccionado = null;
    let filaSeleccionada = null;

    const btnAgregar  = document.getElementById('btnAgregar');
    const btnEditar   = document.getElementById('btnEditar');
    const btnEliminar = document.getElementById('btnEliminar');

    /* =========================
       SELECCIÓN
    ========================= */
    document.querySelectorAll('tr[data-id]').forEach(tr => {
        tr.addEventListener('click', () => {

            document.querySelectorAll('tr')
                .forEach(r => r.classList.remove('selected'));

            tr.classList.add('selected');

            clienteSeleccionado = tr.dataset.id;
            filaSeleccionada = tr;
        });
    });

    /* =========================
       BOTONES
    ========================= */
    btnAgregar.onclick = () => abrirModal(false);

    btnEditar.onclick = () => {
        if (!clienteSeleccionado) return alert('Selecciona un cliente');
        abrirModal(true);
    };

    btnEliminar.onclick = () => {
        if (!clienteSeleccionado) return alert('Selecciona un cliente');
        if (confirm('¿Eliminar cliente?')) {
            location.href = `/operaciones/clientes/eliminar?id=${clienteSeleccionado}`;
        }
    };

    /* =========================
       MODAL
    ========================= */
    function abrirModal(editar) {

        const modal = document.createElement('div');
        modal.className = 'modal';

        modal.innerHTML = `
        <div class="modal-window">
            <div class="modal-header">
                <h2>${editar ? 'Editar cliente' : 'Agregar cliente'}</h2>
            </div>

            <form method="POST" action="/operaciones/clientes/guardar" class="modal-body">

                ${editar ? `<input type="hidden" name="id" value="${clienteSeleccionado}">` : ''}

                <label>Nombre</label>
                <input name="nombre" required
                    value="${editar ? filaSeleccionada.children[1].innerText : ''}">

                <label>Teléfono</label>
                <input name="telefono"
                    value="${editar ? filaSeleccionada.dataset.telefono ?? '' : ''}">

                <label>Email</label>
                <input name="email"
                    value="${editar ? filaSeleccionada.dataset.email ?? '' : ''}">

                <label>Dirección</label>
                <textarea name="direccion">${editar ? filaSeleccionada.dataset.direccion ?? '' : ''}</textarea>

                <label class="checkbox">
                    <input type="checkbox" name="permite_credito"
                        ${editar && filaSeleccionada.dataset.permite_credito == 1 ? 'checked' : ''}>
                    Permitir crédito
                </label>

                <label>Límite de crédito</label>
                <input name="limite_credito" type="number" step="0.01"
                    value="${editar ? filaSeleccionada.dataset.limite_credito ?? 0 : 0}">

                <label>Días de crédito</label>
                <input name="dias_credito" type="number"
                    value="${editar ? filaSeleccionada.dataset.dias_credito ?? 0 : 0}">

                <div class="modal-footer">
                    <button type="submit" class="btn primary">Guardar</button>
                    <button type="button" class="btn danger" id="btnCancelar">Cancelar</button>
                </div>
            </form>
        </div>
        `;

        document.body.appendChild(modal);

        modal.querySelector('#btnCancelar').onclick = () => modal.remove();

        setTimeout(() => {
            const input = modal.querySelector('input[name="nombre"]');
            input.focus();
            if (editar) input.select();
        }, 50);
    }

});
