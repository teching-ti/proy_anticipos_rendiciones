<?php
session_start();

// Cargar la configuración de la base de datos
require_once 'src/config/Database.php';

// Función para cargar controladores
function loadController($controllerName) {
    $controllerFile = 'src/controller/' . ucfirst($controllerName) . 'Controller.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controllerClass = ucfirst($controllerName) . 'Controller';
        return new $controllerClass();
    }
    return null;
}

// Obtener la URL solicitada
// $request_uri = trim($_SERVER['REQUEST_URI'], '/');

$request_uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$base_path = 'proy_anticipos_rendiciones';
$_SESSION['ruta_base'] = 'proy_anticipos_rendiciones';
if (strpos($request_uri, $base_path) === 0) {
    $request_uri = trim(substr($request_uri, strlen($base_path)), '/');
}

$route = $request_uri ?: 'iniciar_sesion';

// Definir rutas
$routes = [
    'iniciar_sesion' => ['controller' => 'login', 'action' => 'index'],
    'login' => ['controller' => 'login', 'action' => 'login'],
    'logout' => ['controller' => 'login', 'action' => 'logout'],
    'dashboard' => ['controller' => 'dashboard', 'action' => 'index'],
    'agregar_usuario' => ['controller' => 'user', 'action' => 'add'],
    'centro_costos' => ['controller' => 'costCenter', 'action' => 'index'],
    'cost_center/add_cc' => ['controller' => 'costCenter', 'action' => 'add_cc'],
    'cost_center/add_scc' => ['controller' => 'costCenter', 'action' => 'add_scc'],
    'cost_center/add_sscc' => ['controller' => 'costCenter', 'action' => 'add_sscc'],
    'cost_center/edit_cc' => ['controller' => 'costCenter', 'action' => 'edit_cc'],
    'cost_center/edit_scc' => ['controller' => 'costCenter', 'action' => 'edit_scc'],
    'cost_center/edit_sscc' => ['controller' => 'costCenter', 'action' => 'edit_sscc'],
    'usuarios' => ['controller' => 'user', 'action' => 'index'],
    'usuarios/searchByDni' => ['controller' => 'user', 'action' => 'searchByDni'],
    'usuarios/anticipoBuscarDni' => ['controller' => 'user', 'action' => 'anticipoBuscarDni'],
    'usuarios/getNumCuenta' => ['controller'=> 'user', 'action' => 'getNumCuenta'],
    'anticipos' => ['controller' => 'anticipo', 'action' => 'index'],
    'anticipos/add' => ['controller' => 'anticipo', 'action' => 'add'],
    'anticipos/update' => ['controller' => 'anticipo', 'action' => 'update'],
    'anticipos/getAllScc' => ['controller' => 'anticipo', 'action' => 'getAllScc'],
    'anticipos/getSsccByScc' => ['controller' => 'anticipo', 'action' => 'getSsccByScc'],
    'anticipos/getSaldoDisponibleTiempoReal' => ['controller' => 'anticipo', 'action' => 'getSaldoDisponibleTiempoReal'],
    'anticipos/getAnticipoDetails' => ['controller' => 'anticipo', 'action' => 'getAnticipoDetails'],
    'anticipos/autorizar' => ['controller' => 'anticipo', 'action' => 'autorizar'],
    'anticipos/autorizacionGerencia' => ['controller' => 'anticipo', 'action' => 'autorizacionGerencia'],
    'anticipos/autorizarTotalmente' => ['controller' => 'anticipo', 'action' => 'autorizarTotalmente'],
    'anticipos/observarAnticipo' => ['controller' => 'anticipo', 'action' => 'observarAnticipo'],
    'anticipos/abonarAnticipo' => ['controller' => 'anticipo', 'action' => 'abonarAnticipo'],
    'anticipos/getComprasMenores' => ['controller' => 'anticipo', 'action' => 'getComprasMenores'], // se usa para descargar en excel
    'anticipos/getViaticos' => ['controller' => 'anticipo', 'action' => 'getViaticos'],
    'anticipos/getTransporteProvincial' => ['controller' => 'anticipo', 'action' => 'getTransporteProvincial'],
    'anticipos/getDocAutorizacion' => ['controller' => 'anticipo', 'action' => 'getDocAutorizacion'], // metodo que se usa para descargar el word con la autorización de descuento
    'anticipos/guardar_adjunto' => ['controller' => 'anticipo', 'action' => 'guardar_adjunto'],
    'anticipos/obtener_adjunto' => ['controller' => 'anticipo', 'action' => 'obtener_adjunto'],
    'anticipos/getAnticipoPendiente' => ['controller' => 'anticipo', 'action' => 'getAnticipoPendiente'],
    'detallesViaticos' => ['controller' => 'anticipo', 'action' => 'detallesViaticos'],
    'tarifario' => ['controller' => 'tarifario', 'action'=> 'index'],
    'tarifario/cargos' => ['controller' => 'tarifario', 'action' => 'obtenerCargos'], //ruta para obtener los cargos del tarifario
    'tarifario/montosCargo' => ['controller' => 'tarifario', 'action' => 'obtenerMontosPorCargo'],
    'tarifario/crearCargo' => ['controller' => 'tarifario', 'action' => 'crearCargo'],
    'tarifario/obtenerMontosParaEditar' => ['controller' => 'tarifario', 'action' => 'obtenerMontosParaEditar'],
    'tarifario/actualizarMontos' => ['controller' => 'tarifario', 'action' => 'actualizarMontos'],
    'tarifario/obtenerCategorias' => ['controller' => 'tarifario', 'action' => 'obtenerCategorias'],
    'tarifario/obtenerDetallesTarifario' => ['controller' => 'tarifario', 'action' => 'obtenerDetallesTarifario'],
    'presupuestos' => ['controller' => 'presupuestoSscc', 'action' => 'index'],
    'presupuestos/get_ccs' => ['controller' => 'presupuestoSscc', 'action' => 'get_ccs'],
    'presupuestos/get_sccs' => ['controller' => 'presupuestoSscc', 'action' => 'get_sccs'],
    'presupuestos/get_ssccs' => ['controller' => 'presupuestoSscc', 'action' => 'get_ssccs'],
    'presupuestos/add' => ['controller' => 'presupuestoSscc', 'action' => 'add'],
    'presupuesto_sscc/add_funds' => ['controller' => 'presupuestoSscc', 'action' => 'add_funds'],
    'rendiciones' => ['controller' => 'rendiciones', 'action' => 'index'],
    'rendiciones/getRendicionDetails' => ['controller' => 'rendiciones', 'action' => 'getRendicionDetails'],
    'rendiciones/getDetallesComprasMenores' => ['controller' => 'rendiciones', 'action' => 'getDetallesComprasMenores'],
    'rendiciones/getDetallesViajes' => ['controller' => 'rendiciones', 'action' => 'getDetallesViajes'],
    'rendiciones/getDetallesRendidosByRendicion' => ['controller' => 'rendiciones', 'action' => 'getDetallesRendidosByRendicion'],
    'rendiciones/getDetallesTransportes' => ['controller'=> 'rendiciones', 'action' => 'getDetallesTransportes'],
    'rendiciones/guardarItemRendido' => ['controller' => 'rendiciones', 'action' => 'guardarItemRendido'],
    'rendiciones/guardarItemViaje' => ['controller' => 'rendiciones', 'action' => 'guardarItemViaje'],
    'rendiciones/guardarItemTransporte' => ['controller' => 'rendiciones', 'action' => 'guardarItemTransporte'],
    'rendiciones/getDetallesViajesRendidosByRendicion' => ['controller' => 'rendiciones', 'action' => 'getDetallesViajesRendidosByRendicion'],
    'rendiciones/getDetallesTransportesRendidosByRendicion' => ['controller' => 'rendiciones', 'action' => 'getDetallesTransportesRendidosByRendicion'],
    'rendiciones/getMontoSolicitadoByAnticipo' => ['controller' => 'rendiciones', 'action' => 'getMontoSolicitadoByAnticipo'],
    'rendiciones/getMontoTotalRendidoByRendicion' => ['controller' => 'rendiciones', 'action' => 'getMontoTotalRendidoByRendicion'],
    'rendiciones/getLatestEstadoRendicion' => ['controller' => 'rendiciones', 'action' => 'getLatestEstadoRendicion'],
    'rendiciones/aprobarRendicion' => ['controller' => 'rendiciones', 'action' => 'aprobarRendicion'],
    'rendiciones/observarRendicion' => ['controller' => 'rendiciones', 'action' => 'observarRendicion'],
    'rendiciones/cerrarRendicion' => ['controller' => 'rendiciones', 'action' => 'cerrarRendicion'],
    'rendiciones/corregirRendicion' => ['controller' => 'rendiciones', 'action' => 'corregirRendicion'],
    'rendiciones/completarRendicion' => ['controller' => 'rendiciones', 'action' => 'completarRendicion'],
    'rendiciones/getComprobantesByDetalle' => ['controller' => 'rendiciones', 'action' => 'getComprobantesByDetalle'],
    'rendiciones/guardarComprobante_compra' => ['controller' => 'rendiciones', 'action' => 'guardarComprobante_compra'],
    'rendiciones/guardarComprobante_viatico' => ['controller' => 'rendiciones', 'action' => 'guardarComprobante_viatico'],
    'rendiciones/guardarComprobante_transporte' => ['controller' => 'rendiciones', 'action' => 'guardarComprobante_transporte'],
    'rendiciones/getMontoTotalRendidoByDetalle' => ['controller' => 'rendiciones', 'action' => 'getMontoTotalRendidoByDetalle'],
    'rendiciones/updateComprobante_compra' => ['controller' => 'rendiciones', 'action' => 'updateComprobante_compra'],
    'rendiciones/updateComprobante_viatico' => ['controller' => 'rendiciones', 'action' => 'updateComprobante_viatico'],
    'rendiciones/updateComprobante_transporte' => ['controller' => 'rendiciones', 'action' => 'updateComprobante_transporte'],
    'rendiciones/getRendicionCompleta' => ['controller' => 'rendiciones', 'action' => 'getRendicionCompleta']
];

// Buscar la ruta en el arreglo de rutas
if (isset($routes[$route])) {
    $controllerName = $routes[$route]['controller'];
    $action = $routes[$route]['action'];
} else {
    header('HTTP/1.0 404 Not Found');
    echo 'Página no encontrada';
    exit;
}

// Cargar el controlador
$controller = loadController($controllerName);
if ($controller && method_exists($controller, $action)) {
    $controller->$action();
} else {
    header('HTTP/1.0 404 Not Found');
    echo 'Controlador o acción no encontrada';
    exit;
}
?>