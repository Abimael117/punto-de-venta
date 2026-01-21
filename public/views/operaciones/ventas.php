<div class="ventas-page">
    <h2 class="module-title">Ventas</h2> 

    <div class="ventas-toolbar">
        <button class="btn primary" id="btnCobrar">💰 Cobrar (F3)</button>

        <button class="btn secondary" id="btnCliente">👤 Cliente (F2)</button>
        <button class="btn warning" id="btnEspera">⏸ Espera (F7)</button>
        <button class="btn info" id="btnRecuperar">▶ Recuperar (F9)</button>
        <button class="btn danger" id="btnRemover">🗑 Remover (F6)</button>
        <button class="btn danger" id="btnCancelar">❌ Cancelar (ESC)</button>
    </div>

    <div class="ventas-layout">

        <div class="table-card ventas-captura-card">
            <div class="ventas-toprow">

                <div class="cliente-pill">
                    <span class="label">Cliente:</span>
                    <strong id="clienteNombre">
                        <?= htmlspecialchars($clienteDefault['nombre'] ?? 'Público en General') ?>
                    </strong>
                    <span class="mini" id="clienteCodigo">
                        <?= htmlspecialchars($clienteDefault['codigo'] ?? '') ?>
                    </span>
                    <input
                        type="hidden"
                        id="clienteId"
                        value="<?= (int)($clienteDefault['id'] ?? 0) ?>"
                        data-limite="<?= htmlspecialchars($clienteDefault['limite_credito'] ?? '0') ?>"
                        data-dias="<?= (int)($clienteDefault['dias_credito'] ?? 0) ?>"
                        data-permite="<?= (int)($clienteDefault['permite_credito'] ?? 0) ?>"
                    >
                </div>

                <div class="panel-captura">
                    <input
                        type="text"
                        id="codigoArticulo"
                        placeholder="Escanea o escribe el código del artículo"
                        autofocus
                    >
                </div>

            </div>
        </div>

        <div class="table-card">
            <table class="tabla-detalle">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Stock</th>
                        <th>Cant.</th>
                        <th>Precio</th>
                        <th>Importe</th>
                    </tr>
                </thead>
                <tbody id="detalleVenta"></tbody>
            </table>

            <div class="totales">
                <div class="total-pill">
                    Total: $ <span id="totalVenta">0.00</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ✅ MODAL: EDITAR CANTIDAD -->
<div class="modal" id="modalCantidad" style="display:none;">
  <div class="modal-window qty-window">
    <div class="modal-header qty-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:18px;">✏️</span>
        <span>Editar cantidad</span>
      </div>
      <button class="prov-close" id="btnCerrarCantidad" type="button">✕</button>
    </div>

    <div class="modal-body">
      <div class="qty-kpis">
        <div class="qty-kpi">
          <div class="qty-kpi-label">Artículo</div>
          <div class="qty-kpi-value" id="qtyArticuloTxt">-</div>
        </div>
        <div class="qty-kpi right">
          <div class="qty-kpi-label">Stock</div>
          <div class="qty-kpi-value" id="qtyStockTxt">0</div>
        </div>
      </div>

      <div class="qty-editor">
        <div class="qty-input-wrap">
          <label class="qty-label" for="qtyCantidad">Cantidad</label>
          <input id="qtyCantidad" class="qty-input" type="text" inputmode="decimal" placeholder="Ej. 12 ó 0.35">
          <div class="qty-hint">Tip: puedes usar punto o coma. Enter = Guardar • Esc = Cancelar</div>
        </div>

        <div class="qty-stepper">
          <button class="btn secondary qty-step" id="btnQtyMenos" type="button">−</button>
          <button class="btn secondary qty-step" id="btnQtyMas" type="button">+</button>
          <button class="btn secondary qty-step" id="btnQtyMenos10" type="button">−0.10</button>
          <button class="btn secondary qty-step" id="btnQtyMas10" type="button">+0.10</button>
        </div>

        <div class="qty-error" id="qtyError" style="display:none;"></div>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn secondary" id="btnCancelarCantidad" type="button">Cancelar</button>
      <button class="btn primary" id="btnGuardarCantidad" type="button">✅ Guardar</button>
    </div>
  </div>
</div>

<!-- MODAL: RECUPERAR VENTA EN ESPERA -->
<div class="modal" id="modalEspera" style="display:none;">
  <div class="modal-window" style="width:760px; max-width:95%;">
    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:10px;">
        <span style="font-size:18px;">⏳</span>
        <span>Recuperar Venta en Espera</span>
      </div>
      <button class="prov-close" id="btnCerrarEspera" type="button">✕</button>
    </div>

    <div class="modal-body">
      <div class="prov-topbar">
        <div class="prov-search" style="flex:1;">
          <span>🔎</span>
          <input id="buscarEspera" type="text" placeholder="Buscar por fecha / cliente / total...">
        </div>
        <button class="btn secondary" id="btnCancelarEspera" type="button">Cancelar</button>
      </div>

      <table class="module-table" id="tablaEspera">
        <thead>
          <tr>
            <th style="width:28%;">Fecha</th>
            <th style="width:16%;">Documento</th>
            <th>Cliente</th>
            <th style="width:16%; text-align:right;">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr><td class="prov-empty" colspan="4">No hay ventas en espera</td></tr>
        </tbody>
      </table>
    </div>

    <div class="modal-footer">
      <button class="btn primary" id="btnAceptarEspera" type="button">✅ Aceptar</button>
    </div>
  </div>
</div>

<!-- MODAL CLIENTES -->
<div id="modalClientes" class="modal" style="display:none;">
    <div class="modal-window" style="width:720px; max-width:95%;">
        <div class="modal-header">
            <div>Seleccionar cliente</div>
            <button class="prov-close" id="btnCerrarClientes" type="button">✖</button>
        </div>

        <div class="modal-body">
            <!-- ✅ Arriba solo buscador -->
            <div class="prov-topbar">
                <div class="prov-search" style="flex:1;">
                    <span>🔎</span>
                    <input type="text" id="buscarCliente" placeholder="Buscar cliente...">
                </div>
            </div>

            <div class="prov-table-wrap">
                <table class="module-table prov-table" id="tablaClientes">
                    <thead>
                        <tr>
                            <th style="width:70px;">#</th>
                            <th>Cliente</th>
                            <th style="width:120px;">Código</th>
                            <th style="width:120px; text-align:right;">Límite</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clientes)): ?>
                            <?php $n=1; foreach ($clientes as $c): ?>
                                <tr
                                    data-id="<?= (int)$c['id'] ?>"
                                    data-text="<?= htmlspecialchars($c['nombre']) ?>"
                                    data-codigo="<?= htmlspecialchars($c['codigo'] ?? '') ?>"
                                    data-limite="<?= htmlspecialchars($c['limite_credito'] ?? '0') ?>"
                                    data-permite="<?= (int)($c['permite_credito'] ?? 0) ?>"
                                    data-dias="<?= (int)($c['dias_credito'] ?? 0) ?>"
                                >
                                    <td><?= $n++ ?></td>
                                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                                    <td><?= htmlspecialchars($c['codigo'] ?? '') ?></td>
                                    <td style="text-align:right;"><?= number_format((float)($c['limite_credito'] ?? 0),2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="prov-empty">No hay clientes</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ✅ Footer como POS: Seleccionar / Crear / Cancelar -->
        <div class="modal-footer">
            <button class="btn primary" id="btnSeleccionarCliente" type="button">✅ Seleccionar</button>
            <button class="btn secondary" id="btnCrearCliente" type="button">➕ Crear</button>
            <button class="btn danger" id="btnCancelarClientes" type="button">❌ Cancelar</button>
        </div>
    </div>
</div>

<!-- MODAL: CREAR CLIENTE (rápido) -->
<div class="modal cc-modal" id="modalCrearCliente" style="display:none;">
  <div class="modal-window cc-window">
    
    <div class="cc-header">
      <div class="cc-title">
        <span class="cc-ico">➕</span>
        <span>Crear cliente</span>
      </div>

      <button class="cc-close" id="btnCerrarCrearCliente" type="button" aria-label="Cerrar">✕</button>
    </div>

    <div class="cc-body">

      <div class="cc-grid">
        <div class="cc-field cc-span-2">
          <label class="cc-label" for="cc_nombre">Nombre <span class="cc-req">*</span></label>
          <input class="cc-input" type="text" id="cc_nombre" placeholder="Ej. Juan Pérez">
        </div>

        <div class="cc-field">
          <label class="cc-label" for="cc_telefono">Teléfono</label>
          <input class="cc-input" type="text" id="cc_telefono" placeholder="Opcional">
        </div>

        <div class="cc-field">
          <label class="cc-label" for="cc_email">Email</label>
          <input class="cc-input" type="text" id="cc_email" placeholder="Opcional">
        </div>

        <div class="cc-field cc-span-2">
          <label class="cc-label" for="cc_direccion">Dirección</label>
          <input class="cc-input" type="text" id="cc_direccion" placeholder="Opcional">
        </div>

        <div class="cc-field">
          <label class="cc-label" for="cc_limite">Límite de crédito</label>
          <input class="cc-input" type="number" step="0.01" id="cc_limite" value="0">
        </div>

        <div class="cc-field">
          <label class="cc-label" for="cc_dias">Días de crédito</label>
          <input class="cc-input" type="number" id="cc_dias" value="0" min="0">
        </div>

        <div class="cc-check cc-span-2">
          <label class="cc-check-label">
            <input class="cc-check-input" type="checkbox" id="cc_permite" checked>
            <span>Permitir crédito</span>
          </label>
        </div>
      </div>

      <div class="cc-alert" id="cc_error" style="display:none;"></div>
    </div>

    <div class="cc-footer">
      <button class="btn primary cc-btn" id="btnGuardarClienteRapido" type="button">💾 Guardar</button>
      <button class="btn danger cc-btn" id="btnCancelarCrearCliente" type="button">Cancelar</button>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/modal_cobro.php'; ?>
<script src="/public/assets/js/operaciones/ventas.js"></script>
