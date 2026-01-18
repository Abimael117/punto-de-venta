<?php
// Protección cuando no hay registro en BD
$empresa = $empresa ?: [
    'nombre'    => '',
    'direccion' => '',
    'ciudad'    => '',
    'estado'    => '',
    'cp'        => '',
    'telefono'  => '',
    'email'     => '',
];
?>

<div class="empresa-container">

    <h1 class="empresa-title">Empresa</h1>

    <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alert-success">
            <?= $_SESSION['flash']; unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <form action="/configuracion/empresa/guardar" method="POST" class="empresa-form">

        <div class="empresa-group">
            <label>Nombre de la tienda</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($empresa['nombre']) ?>" required>
        </div>

        <div class="empresa-group">
            <label>Dirección</label>
            <input type="text" name="direccion" value="<?= htmlspecialchars($empresa['direccion']) ?>">
        </div>

        <div class="empresa-row">
            <div class="empresa-group">
                <label>Ciudad</label>
                <input type="text" name="ciudad" value="<?= htmlspecialchars($empresa['ciudad']) ?>">
            </div>

            <div class="empresa-group">
                <label>Estado</label>
                <input type="text" name="estado" value="<?= htmlspecialchars($empresa['estado']) ?>">
            </div>

            <div class="empresa-group">
                <label>C.P.</label>
                <input type="text" name="cp" value="<?= htmlspecialchars($empresa['cp']) ?>">
            </div>
        </div>

        <div class="empresa-row">
            <div class="empresa-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?= htmlspecialchars($empresa['telefono']) ?>">
            </div>

            <div class="empresa-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($empresa['email']) ?>">
            </div>
        </div>

        <div class="empresa-actions">
            <button type="submit">Guardar</button>
        </div>

    </form>
</div>
