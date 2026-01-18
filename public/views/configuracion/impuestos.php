<div class="module-container">

    <h2 class="module-title">Impuestos</h2>

    <form method="POST" action="/configuracion/impuestos/guardar" class="form-row">
        <input type="text" name="nombre" placeholder="Nombre (ej. I.V.A.)" required>

        <input type="number" step="0.01" name="tasa" placeholder="Tasa (ej. 16)">

        <select name="tras_ret" class="select">
            <option value="TRAS">TRAS</option>
            <option value="RET">RET</option>
        </select>

        <select name="tipo" class="select">
            <option value="TASA">Tasa</option>
            <option value="CUOTA">Cuota</option>
            <option value="EXENTO">Exento</option>
        </select>

        <label class="check">
            <input type="checkbox" name="aplica_iva" checked>
            Aplica I.V.A.
        </label>

        <label class="check">
            <input type="checkbox" name="impreso" checked>
            Impreso
        </label>

        <label class="check">
            <input type="checkbox" name="activo" checked>
            Activo
        </label>

        <input type="text" name="cuenta_contable" placeholder="Cuenta contable (opcional)">
        <input type="number" name="orden" placeholder="Orden" value="0">

        <button type="submit" class="btn-primary">Guardar</button>
    </form>

    <div class="table-card">
        <table class="module-table">
            <thead>
                <tr>
                    <th style="width:70px;">ID</th>
                    <th>Nombre</th>
                    <th style="width:90px; text-align:right;">Impuesto</th>
                    <th style="width:90px;">Tras/Ret</th>
                    <th style="width:90px; text-align:center;">Apl I.V.A.</th>
                    <th style="width:90px; text-align:center;">Impreso</th>
                    <th style="width:90px; text-align:center;">Activo</th>
                    <th style="width:100px;">Tipo</th>
                    <th>Cuenta Contable</th>
                    <th style="width:80px; text-align:right;">Orden</th>
                    <th style="width:110px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($impuestos as $imp): ?>
                    <tr>
                        <td><?= (int)$imp['id'] ?></td>
                        <td><?= htmlspecialchars($imp['nombre']) ?></td>
                        <td style="text-align:right; font-variant-numeric: tabular-nums;">
                            <?= number_format((float)$imp['tasa'], 2) ?>
                        </td>
                        <td><?= htmlspecialchars($imp['tras_ret']) ?></td>

                        <td style="text-align:center;">
                            <span class="dot <?= !empty($imp['aplica_iva']) ? 'on' : 'off' ?>"></span>
                        </td>

                        <td style="text-align:center;">
                            <span class="dot <?= !empty($imp['impreso']) ? 'on' : 'off' ?>"></span>
                        </td>

                        <td style="text-align:center;">
                            <?php if (!empty($imp['activo'])): ?>
                                <span class="badge active">Activo</span>
                            <?php else: ?>
                                <span class="badge inactive">Inactivo</span>
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars($imp['tipo']) ?></td>
                        <td><?= htmlspecialchars($imp['cuenta_contable'] ?? '') ?></td>
                        <td style="text-align:right; font-variant-numeric: tabular-nums;">
                            <?= (int)($imp['orden'] ?? 0) ?>
                        </td>

                        <td>
                            <a class="action-link danger"
                               href="/configuracion/impuestos/toggle?id=<?= (int)$imp['id'] ?>">
                               <?= !empty($imp['activo']) ? 'Desactivar' : 'Activar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($impuestos)): ?>
                    <tr>
                        <td colspan="11" style="text-align:center; padding:18px; color:#666;">
                            No hay impuestos registrados
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
