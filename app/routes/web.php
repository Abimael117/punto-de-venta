<?php

$routes = [

    '/' => [
        'controller' => 'DashboardController',
        'action' => 'index'
    ],

    '/dashboard/data' => [
        'controller' => 'DashboardController',
        'action'     => 'data'
    ],


    '/login' => [
        'controller' => 'AuthController',
        'action' => 'loginView'
    ],

    '/login/post' => [
        'controller' => 'AuthController',
        'action' => 'login'
    ],

    '/logout' => [
        'controller' => 'AuthController',
        'action' => 'logout'
    ],

    '/operaciones' => [
        'controller' => 'OperacionesController',
        'action' => 'index'
    ],

    '/consultas' => [
        'controller' => 'ConsultasController',
        'action' => 'index'
    ],

    // =========================
    // CONSULTAS - VENTAS DETALLE
    // =========================
    '/consultas/ventas-detalle' => [
        'controller' => 'consultas/VentasDetalleController',
        'action'     => 'index'
    ],

    '/consultas/ventas-detalle/listar' => [
        'controller' => 'consultas/VentasDetalleController',
        'action'     => 'listar'
    ],

    '/consultas/ventas-detalle/detalle' => [
        'controller' => 'consultas/VentasDetalleController',
        'action'     => 'detalle'
    ],

    '/procesos' => [
        'controller' => 'ProcesosController',
        'action' => 'index'
    ],

    '/reportes' => [
        'controller' => 'ReportesController',
        'action' => 'index'
    ],

    '/estadisticas' => [
        'controller' => 'EstadisticasController',
        'action'     => 'index'
    ],

    '/configuracion' => [
        'controller' => 'ConfiguracionController',
        'action' => 'index'
    ],

    '/configuracion/empresa' => [
        'controller' => 'configuracion/EmpresaController',
        'action' => 'index'
    ],

    '/configuracion/empresa/guardar' => [
        'controller' => 'configuracion/EmpresaController',
        'action' => 'guardar'
    ],

    '/configuracion/categorias' => [
        'controller' => 'configuracion/CategoriasController',
        'action' => 'index'
    ],

    '/configuracion/categorias/guardar' => [
        'controller' => 'configuracion/CategoriasController',
        'action' => 'guardar'
    ],

    '/configuracion/categorias/toggle' => [
        'controller' => 'configuracion/CategoriasController',
        'action' => 'toggle'
    ],

    // =========================
    // CONSULTAS - VENTAS A CRÉDITO (TOTAL POR CLIENTE)
    // =========================
    '/consultas/ventas-credito' => [
        'controller' => 'consultas/VentasCreditoController',
        'action'     => 'index'
    ],

    '/consultas/ventas-credito/listar' => [
        'controller' => 'consultas/VentasCreditoController',
        'action'     => 'listar'
    ],

    // Detalle por cliente (desglose de créditos)
    '/consultas/ventas-credito/detalle' => [
        'controller' => 'consultas/VentasCreditoController',
        'action'     => 'detalle'
    ],

    // Detalle de un crédito (incluye pagos/abonos)
    '/consultas/ventas-credito/credito' => [
        'controller' => 'consultas/VentasCreditoController',
        'action'     => 'credito'
    ],

    // =========================
    // CONSULTAS - COMPRAS A CRÉDITO
    // =========================

    '/consultas/compras-credito' => [
        'controller' => 'consultas/ComprasCreditoController',
        'action'     => 'index'
    ],

    '/consultas/compras-credito/listar' => [
        'controller' => 'consultas/ComprasCreditoController',
        'action'     => 'listar'
    ],

    '/consultas/compras-credito/detalle' => [
        'controller' => 'consultas/ComprasCreditoController',
        'action'     => 'detalle'
    ],

    '/consultas/compras-credito/abonar' => [
        'controller' => 'consultas/ComprasCreditoController',
        'action'     => 'abonar'
    ],

    '/consultas/compras-credito/marcarPagada' => [
        'controller' => 'consultas/ComprasCreditoController',
        'action'     => 'marcarPagada'
    ],


    // =========================
    // CONFIGURACIÓN - UNIDADES
    // =========================

    '/configuracion/unidades' => [
        'controller' => 'configuracion/UnidadesController',
        'action'     => 'index'
    ],

    '/configuracion/unidades/guardar' => [
        'controller' => 'configuracion/UnidadesController',
        'action'     => 'guardar'
    ],

    '/configuracion/unidades/toggle' => [
        'controller' => 'configuracion/UnidadesController',
        'action'     => 'toggle'
    ],
    // =========================
    // CONFIGURACIÓN - CAJAS
    // =========================

    '/configuracion/cajas' => [
        'controller' => 'configuracion/CajasController',
        'action'     => 'index'
    ],

    '/configuracion/cajas/guardar' => [
        'controller' => 'configuracion/CajasController',
        'action'     => 'guardar'
    ],

    '/configuracion/cajas/toggle' => [
        'controller' => 'configuracion/CajasController',
        'action'     => 'toggle'
    ],

    // =========================
    // CONFIGURACIÓN - USUARIOS
    // =========================

    '/configuracion/usuarios' => [
        'controller' => 'configuracion/UsuariosController',
        'action'     => 'index'
    ],

    '/configuracion/usuarios/guardar' => [
        'controller' => 'configuracion/UsuariosController',
        'action'     => 'guardar'
    ],

    '/configuracion/usuarios/toggle' => [
        'controller' => 'configuracion/UsuariosController',
        'action'     => 'toggle'
    ],

    // =========================
    // CONFIGURACIÓN - ROLES
    // =========================

    '/configuracion/roles' => [
        'controller' => 'configuracion/RolesController',
        'action'     => 'index'
    ],

    '/configuracion/roles/guardar' => [
        'controller' => 'configuracion/RolesController',
        'action'     => 'guardar'
    ],

    '/configuracion/roles/toggle' => [
        'controller' => 'configuracion/RolesController',
        'action'     => 'toggle'
    ],

    // =========================
    // CONFIGURACIÓN - EMPLEADOS
    // =========================

    '/configuracion/empleados' => [
        'controller' => 'configuracion/EmpleadosController',
        'action'     => 'index'
    ],

    '/configuracion/empleados/guardar' => [
        'controller' => 'configuracion/EmpleadosController',
        'action'     => 'guardar'
    ],

    '/configuracion/empleados/toggle' => [
        'controller' => 'configuracion/EmpleadosController',
        'action'     => 'toggle'
    ],

    // =========================
    // CONFIGURACIÓN - IMPRESORAS
    // =========================

    '/configuracion/impresoras' => [
        'controller' => 'configuracion/ImpresorasController',
        'action'     => 'index'
    ],

    '/configuracion/impresoras/guardar' => [
        'controller' => 'configuracion/ImpresorasController',
        'action'     => 'guardar'
    ],

    '/configuracion/impresoras/toggle' => [
        'controller' => 'configuracion/ImpresorasController',
        'action'     => 'toggle'
    ],

    // =========================
    // CONFIGURACIÓN - IMPRESORAS POR CAJA
    // =========================

    '/configuracion/caja-impresora' => [
        'controller' => 'configuracion/CajaImpresoraController',
        'action'     => 'index'
    ],

    '/configuracion/caja-impresora/guardar' => [
        'controller' => 'configuracion/CajaImpresoraController',
        'action'     => 'guardar'
    ],

    '/configuracion/caja-impresora/toggle' => [
        'controller' => 'configuracion/CajaImpresoraController',
        'action'     => 'toggle'
    ],

    // =========================
    // CONFIGURACIÓN - TAGS
    // =========================

    '/configuracion/tags' => [
        'controller' => 'configuracion/TagsController',
        'action'     => 'index'
    ],

    '/configuracion/tags/guardar' => [
        'controller' => 'configuracion/TagsController',
        'action'     => 'guardar'
    ],

    '/configuracion/tags/toggle' => [
        'controller' => 'configuracion/TagsController',
        'action'     => 'toggle'
    ],

    // =========================
    // CONFIGURACIÓN - MONEDA
    // =========================

    '/configuracion/moneda' => [
        'controller' => 'configuracion/MonedaController',
        'action'     => 'index'
    ],

    '/configuracion/moneda/guardar' => [
        'controller' => 'configuracion/MonedaController',
        'action'     => 'guardar'
    ],

    '/configuracion/moneda/activar' => [
        'controller' => 'configuracion/MonedaController',
        'action'     => 'activar'
    ],

    // =========================
    // OPERACIONES - PROVEEDORES
    // =========================

    '/operaciones/proveedores' => [
        'controller' => 'operaciones/ProveedoresController',
        'action'     => 'index'
    ],

    '/operaciones/proveedores/guardar' => [
        'controller' => 'operaciones/ProveedoresController',
        'action'     => 'guardar'
    ],

    '/operaciones/proveedores/toggle' => [
        'controller' => 'operaciones/ProveedoresController',
        'action'     => 'toggle'
    ],

    // =========================
    // OPERACIONES - ARTICULOS
    // =========================

    '/operaciones/articulos' => [
        'controller' => 'operaciones/ArticulosController',
        'action'     => 'index'
    ],

    '/operaciones/articulos/guardar' => [
        'controller' => 'operaciones/ArticulosController',
        'action'     => 'guardar'
    ],

    '/operaciones/articulos/toggle' => [
        'controller' => 'operaciones/ArticulosController',
        'action'     => 'toggle'
    ],

    '/operaciones/articulos/eliminar' => [
        'controller' => 'operaciones/ArticulosController',
        'action'     => 'eliminar'
    ],

    // =========================
    // OPERACIONES - CLIENTES
    // =========================

    '/operaciones/clientes' => [
        'controller' => 'operaciones/ClientesController',
        'action' => 'index'
    ],

    '/operaciones/clientes/guardar' => [
        'controller' => 'operaciones/ClientesController',
        'action' => 'guardar'
    ],

    '/operaciones/clientes/eliminar' => [
        'controller' => 'operaciones/ClientesController',
        'action' => 'eliminar'
    ],

    // =========================
    // OPERACIONES - COMPRAS
    // =========================

    '/operaciones/compras' => [
        'controller' => 'operaciones/ComprasController',
        'action'     => 'index'
    ],
    
    '/operaciones/compras/guardarProveedor' => [
        'controller' => 'operaciones/ComprasController',
        'action'     => 'guardarProveedor'
    ],


    '/operaciones/compras/guardar' => [
        'controller' => 'operaciones/ComprasController',
        'action'     => 'guardar'
    ],

    '/operaciones/articulos/buscar' => [
        'controller' => 'operaciones/ArticulosController',
        'action'     => 'buscarPorCodigo'
    ],

    // =========================
    // OPERACIONES - VENTAS
    // =========================

    '/operaciones/ventas' => [
        'controller' => 'operaciones/VentasController',
        'action'     => 'index'
    ],

    '/operaciones/ventas/guardar' => [
        'controller' => 'operaciones/VentasController',
        'action'     => 'guardar'
    ],

    '/operaciones/ventas/buscar-articulo' => [
        'controller' => 'operaciones/ArticulosController',
        'action'     => 'buscarPorCodigo'
    ],

    '/operaciones/ventas/creditoInfo' => [
        'controller' => 'operaciones/VentasController',
        'action'     => 'creditoInfo'
    ],


    // =========================
    // CONFIGURACIÓN - IMPUESTOS
    // =========================

    '/configuracion/impuestos' => [
        'controller' => 'configuracion/ImpuestosController',
        'action'     => 'index'
    ],

    '/configuracion/impuestos/guardar' => [
        'controller' => 'configuracion/ImpuestosController',
        'action'     => 'guardar'
    ],

    '/configuracion/impuestos/toggle' => [
        'controller' => 'configuracion/ImpuestosController',
        'action'     => 'toggle'
    ],

    // =========================
    // OPERACIONES - CLIENTES
    // =========================
    '/operaciones/clientes' => [
        'controller' => 'operaciones/ClientesController',
        'action'     => 'index'
    ],

    '/operaciones/clientes/listar' => [
        'controller' => 'operaciones/ClientesController',
        'action'     => 'listar'
    ],

    '/operaciones/clientes/guardar' => [
        'controller' => 'operaciones/ClientesController',
        'action'     => 'guardar'
    ],

    '/operaciones/clientes/actualizar' => [
        'controller' => 'operaciones/ClientesController',
        'action'     => 'actualizar'
    ],

    '/operaciones/clientes/eliminar' => [
        'controller' => 'operaciones/ClientesController',
        'action'     => 'eliminar'
    ],

    '/operaciones/clientes/obtener' => [
        'controller' => 'operaciones/ClientesController',
        'action'     => 'obtener'
    ],

    '/operaciones/clientes/crearRapido' => [
        'controller' => 'operaciones/ClientesController',
        'action'     => 'crearRapido'
    ],

    // =========================
    // OPERACIONES - CORTE DE CAJA
    // =========================
    '/operaciones/corte-caja' => [
        'controller' => 'operaciones/CorteCajaController',
        'action'     => 'index'
    ],

    '/operaciones/corte-caja/resumen' => [
        'controller' => 'operaciones/CorteCajaController',
        'action'     => 'resumen'
    ],

    '/operaciones/corte-caja/guardar' => [
        'controller' => 'operaciones/CorteCajaController',
        'action'     => 'guardar'
    ],

    '/operaciones/corte-caja/listar' => [
        'controller' => 'operaciones/CorteCajaController',
        'action'     => 'listar'
    ],

    '/operaciones/corte-caja/detalle' => [
        'controller' => 'operaciones/CorteCajaController',
        'action'     => 'detalle'
    ],

    // =========================
    // OPERACIONES - CUENTAS POR COBRAR (CRÉDITOS)
    // =========================
    '/operaciones/cuentas-por-cobrar' => [
        'controller' => 'operaciones/CuentasCobrarController',
        'action'     => 'index'
    ],

    '/operaciones/cuentas-por-cobrar/listar' => [
        'controller' => 'operaciones/CuentasCobrarController',
        'action'     => 'listar'
    ],

    '/operaciones/cuentas-por-cobrar/detalle' => [
        'controller' => 'operaciones/CuentasCobrarController',
        'action'     => 'detalle'
    ],

    '/operaciones/cuentas-por-cobrar/abonar' => [
        'controller' => 'operaciones/CuentasCobrarController',
        'action'     => 'abonar'
    ],

    // =========================
    // OPERACIONES - CUENTAS POR PAGAR
    // =========================
    '/operaciones/cuentas-por-pagar' => [
        'controller' => 'operaciones/CuentasPagarController',
        'action'     => 'index'
    ],

    '/operaciones/cuentas-por-pagar/listar' => [
        'controller' => 'operaciones/CuentasPagarController',
        'action'     => 'listar'
    ],

    '/operaciones/cuentas-por-pagar/detalle' => [
        'controller' => 'operaciones/CuentasPagarController',
        'action'     => 'detalle'
    ],

    '/operaciones/cuentas-por-pagar/abonar' => [
        'controller' => 'operaciones/CuentasPagarController',
        'action'     => 'abonar'
    ],

    '/operaciones/cuentas-por-pagar/cancelar' => [
        'controller' => 'operaciones/CuentasPagarController',
        'action'     => 'cancelar'
    ],

    // =========================
    // OPERACIONES - INVENTARIO INICIAL
    // =========================
    '/operaciones/inventario-inicial' => [
        'controller' => 'operaciones/InventarioInicialController',
        'action'     => 'index'
    ],

    '/operaciones/inventario-inicial/listar' => [
        'controller' => 'operaciones/InventarioInicialController',
        'action'     => 'listar'
    ],

    '/operaciones/inventario-inicial/detalle' => [
        'controller' => 'operaciones/InventarioInicialController',
        'action'     => 'detalle'
    ],

    '/operaciones/inventario-inicial/guardar' => [
        'controller' => 'operaciones/InventarioInicialController',
        'action'     => 'guardar'
    ],

    // =========================
    // OPERACIONES - AJUSTE DE INVENTARIO
    // =========================
    '/operaciones/ajuste-inventario' => [
        'controller' => 'operaciones/AjusteInventarioController',
        'action'     => 'index'
    ],

    '/operaciones/ajuste-inventario/listar' => [
        'controller' => 'operaciones/AjusteInventarioController',
        'action'     => 'listar'
    ],

    '/operaciones/ajuste-inventario/detalle' => [
        'controller' => 'operaciones/AjusteInventarioController',
        'action'     => 'detalle'
    ],

    '/operaciones/ajuste-inventario/guardar' => [
        'controller' => 'operaciones/AjusteInventarioController',
        'action'     => 'guardar'
    ],

    '/operaciones/ajuste-inventario/articulos' => [
        'controller' => 'operaciones/AjusteInventarioController',
        'action'     => 'articulos'
    ],

];

return $routes;
