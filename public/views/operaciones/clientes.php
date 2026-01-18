<div class="toolbar">
    <button id="btnAgregar">➕ Agregar (F3)</button>
    <button id="btnEditar">✏️ Editar (F4)</button>
    <button id="btnEliminar">❌ Eliminar (F6)</button>
</div>

<table class="tabla">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Crédito</th>
            <th>Límite</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clientes as $c): ?>
            <tr data-id="<?= $c['id'] ?>">
                <td><?= $c['codigo'] ?></td>
                <td><?= $c['nombre'] ?></td>
                <td><?= $c['telefono'] ?></td>
                <td><?= $c['permite_credito'] ? 'Sí' : 'No' ?></td>
                <td>$<?= number_format($c['limite_credito'],2) ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>

<script src="/public/assets/js/operaciones/clientes.js"></script>
