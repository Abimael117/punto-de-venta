<?php

require_once __DIR__ . '/InventarioMovimientosModel.php';
require_once __DIR__ . '/ClientesModel.php';

class VentasModel {

    private $db;
    private $inventario;
    private $clientes;

    public function __construct() {
        $this->db = Database::connect();
        $this->inventario = new InventarioMovimientosModel();
        $this->clientes = new ClientesModel();
    }

    public function generarFolio() {
        return 'VTA-' . date('Ymd-His');
    }

    private function getUserId() {
        if (!empty($_SESSION['usuario']['id'])) return (int)$_SESSION['usuario']['id'];
        if (!empty($_SESSION['user']['id']))    return (int)$_SESSION['user']['id'];
        if (!empty($_SESSION['user_id']))       return (int)$_SESSION['user_id'];
        if (!empty($_SESSION['usuario_id']))    return (int)$_SESSION['usuario_id'];
        return null;
    }

    private function getCajaId() {
        if (!empty($_SESSION['caja_id'])) return (int)$_SESSION['caja_id'];
        if (!empty($_SESSION['caja']['id'])) return (int)$_SESSION['caja']['id'];
        return null;
    }

    private function getSaldoPendienteCredito($clienteId) {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(saldo), 0) AS saldo_pendiente
            FROM creditos
            WHERE cliente_id = :c
              AND estado = 'PENDIENTE'
              AND saldo > 0
        ");
        $stmt->execute([':c' => (int)$clienteId]);
        return (float)$stmt->fetchColumn();
    }

    private function tieneCreditosVencidos($clienteId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM creditos
            WHERE cliente_id = :c
              AND estado = 'PENDIENTE'
              AND saldo > 0
              AND fecha_vencimiento < CURDATE()
        ");
        $stmt->execute([':c' => (int)$clienteId]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    /**
     * Validar SOLO el monto que se irá a crédito (restante)
     */
    private function validarCredito($clienteId, $montoCredito) {

        $cli = $this->clientes->getById($clienteId);
        if (!$cli) throw new Exception('Cliente no válido');

        if ((int)($cli['activo'] ?? 1) !== 1) throw new Exception('Cliente inactivo');
        if ((int)($cli['permite_credito'] ?? 0) !== 1) throw new Exception('Este cliente NO tiene permitido crédito');

        if ($this->tieneCreditosVencidos($clienteId)) {
            throw new Exception('Cliente con deuda vencida: NO se permite fiado');
        }

        $limite     = (float)($cli['limite_credito'] ?? 0);
        $pendiente  = $this->getSaldoPendienteCredito($clienteId);
        $disponible = $limite - $pendiente;

        if ($limite <= 0) throw new Exception('Cliente sin límite de crédito configurado');

        $montoCredito = (float)$montoCredito;
        if ($montoCredito <= 0) return; // no hay crédito real

        if ($montoCredito > $disponible) {
            throw new Exception('Crédito insuficiente: excede el disponible del cliente');
        }
    }

    /**
     * CREAR VENTA (CONTADO / MIXTO / CRÉDITO)
     */
    public function crearVenta($venta, $detalle) {

        $this->db->beginTransaction();

        try {

            // 1) Recalcular total real
            $total = 0;
            foreach ($detalle as $it) {
                $cant   = (float)($it['cantidad'] ?? 0);
                $precio = (float)($it['precio'] ?? 0);
                if ($cant <= 0) continue;
                $total += ($cant * $precio);
            }
            $total = round($total, 2);

            if ($total <= 0) throw new Exception('Total inválido');

            // 2) Datos cliente
            $clienteId = isset($venta['cliente_id']) ? (int)$venta['cliente_id'] : 0;
            $clienteId = $clienteId > 0 ? $clienteId : null;

            // 3) Pagos reales
            $efectivo      = (float)($venta['efectivo'] ?? 0);
            $tarjeta       = (float)($venta['tarjeta'] ?? 0);
            $transferencia = (float)($venta['transferencia'] ?? 0);
            $referencia    = trim((string)($venta['referencia'] ?? ''));

            $pagoTotal = round($efectivo + $tarjeta + $transferencia, 2);

            // 4) Restante a crédito
            $restanteCredito = round($total - $pagoTotal, 2);
            if ($restanteCredito < 0) $restanteCredito = 0;

            $esCredito = ($restanteCredito > 0);

            // 5) Validaciones:
            if ($esCredito) {
                if (!$clienteId) throw new Exception('Selecciona un cliente para fiado');
                $this->validarCredito((int)$clienteId, $restanteCredito);
            } else {
                // contado puro: debe cubrir total
                if ($pagoTotal < $total) throw new Exception('Pago insuficiente');
            }

            $usuarioId = $this->getUserId();
            $cajaId    = $this->getCajaId();

            // 6) Insert venta
            $stmt = $this->db->prepare("
                INSERT INTO ventas
                    (folio, cliente_id, usuario_id, caja_id, total, es_credito, efectivo, tarjeta, transferencia, referencia)
                VALUES
                    (:folio, :cliente_id, :usuario_id, :caja_id, :total, :es_credito, :efectivo, :tarjeta, :transferencia, :referencia)
            ");

            $stmt->execute([
                ':folio'         => $this->generarFolio(),
                ':cliente_id'    => $clienteId,
                ':usuario_id'    => $usuarioId,
                ':caja_id'       => $cajaId,
                ':total'         => $total,
                ':es_credito'    => $esCredito ? 1 : 0,
                ':efectivo'      => $efectivo,
                ':tarjeta'       => $tarjeta,
                ':transferencia' => $transferencia,
                ':referencia'    => ($referencia !== '' ? $referencia : null),
            ]);

            $ventaId = (int)$this->db->lastInsertId();

            // 7) Detalle + inventario
            $stmtDet = $this->db->prepare("
                INSERT INTO ventas_detalle
                    (venta_id, articulo_id, cantidad, precio, importe)
                VALUES
                    (:venta_id, :articulo_id, :cantidad, :precio, :importe)
            ");

            foreach ($detalle as $item) {

                $articuloId = (int)($item['articulo_id'] ?? 0);
                $cantidad   = (float)($item['cantidad'] ?? 0);
                $precio     = (float)($item['precio'] ?? 0);

                if ($articuloId <= 0 || $cantidad <= 0) continue;

                $importe = round($cantidad * $precio, 2);

                $stmtDet->execute([
                    ':venta_id'    => $ventaId,
                    ':articulo_id' => $articuloId,
                    ':cantidad'    => $cantidad,
                    ':precio'      => $precio,
                    ':importe'     => $importe
                ]);

                $this->inventario->salida(
                    $articuloId,
                    'VENTA',
                    $ventaId,
                    $cantidad
                );
            }

            // 8) Si hay restante a crédito: crear registro en creditos SOLO por ese monto
            if ($esCredito) {

                $cli  = $this->clientes->getById((int)$clienteId);
                $dias = (int)($cli['dias_credito'] ?? 0);
                if ($dias <= 0) $dias = 1;

                $stmt = $this->db->prepare("
                    INSERT INTO creditos
                        (venta_id, cliente_id, usuario_id, total, saldo, dias_credito, fecha_vencimiento, estado)
                    VALUES
                        (:venta_id, :cliente_id, :usuario_id, :total, :saldo, :dias_credito,
                         DATE_ADD(CURDATE(), INTERVAL :dias DAY), 'PENDIENTE')
                ");

                $stmt->execute([
                    ':venta_id'     => $ventaId,
                    ':cliente_id'   => (int)$clienteId,
                    ':usuario_id'   => $usuarioId,
                    ':total'        => $restanteCredito,
                    ':saldo'        => $restanteCredito,
                    ':dias_credito' => $dias,
                    ':dias'         => $dias
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // =========================
    // AJAX: Adeudo actual
    // =========================
    public function getAdeudoCliente($clienteId) {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(saldo),0)
            FROM creditos
            WHERE cliente_id = :c
              AND estado = 'PENDIENTE'
              AND saldo > 0
        ");
        $stmt->execute([':c' => (int)$clienteId]);
        return (float)$stmt->fetchColumn();
    }

    // =========================
    // AJAX: ¿tiene vencidos?
    // =========================
    public function clienteTieneVencidosPublic($clienteId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM creditos
            WHERE cliente_id = :c
              AND estado = 'PENDIENTE'
              AND saldo > 0
              AND fecha_vencimiento < CURDATE()
        ");
        $stmt->execute([':c' => (int)$clienteId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getCreditoResumen($clienteId) {

        // Próximo vencimiento de créditos PENDIENTES (el más cercano)
        $stmt = $this->db->prepare("
            SELECT 
                MIN(fecha_vencimiento) AS proximo_vencimiento,
                COALESCE(SUM(saldo),0) AS adeudo
            FROM creditos
            WHERE cliente_id = :c
            AND estado = 'PENDIENTE'
            AND saldo > 0
        ");
        $stmt->execute([':c' => (int)$clienteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $proximo = $row['proximo_vencimiento'] ?? null;
        $adeudo  = (float)($row['adeudo'] ?? 0);

        // tiene vencidos
        $stmt2 = $this->db->prepare("
            SELECT COUNT(*)
            FROM creditos
            WHERE cliente_id = :c
            AND estado = 'PENDIENTE'
            AND saldo > 0
            AND fecha_vencimiento < CURDATE()
        ");
        $stmt2->execute([':c' => (int)$clienteId]);
        $tieneVencidos = ((int)$stmt2->fetchColumn()) > 0;

        $diasRestantes = null;

        if ($proximo) {
            // días restantes hacia el próximo vencimiento (puede ser negativo)
            $stmt3 = $this->db->prepare("SELECT DATEDIFF(:fv, CURDATE())");
            $stmt3->execute([':fv' => $proximo]);
            $diasRestantes = (int)$stmt3->fetchColumn();
        }

        return [
            'adeudo'           => $adeudo,
            'tieneVencidos'    => $tieneVencidos,
            'proximoVenc'      => $proximo,        // YYYY-MM-DD o null
            'diasRestantes'    => $diasRestantes   // int o null
        ];
    }

}
