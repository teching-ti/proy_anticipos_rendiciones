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
    'centro_costos' => ['controller' => 'costcenter', 'action' => 'index'],
    'cost_center/add_cc' => ['controller' => 'costcenter', 'action' => 'add_cc'],
    'cost_center/add_scc' => ['controller' => 'costcenter', 'action' => 'add_scc'],
    'cost_center/add_sscc' => ['controller' => 'costcenter', 'action' => 'add_sscc'],
    'cost_center/edit_cc' => ['controller' => 'costcenter', 'action' => 'edit_cc'],
    'cost_center/edit_scc' => ['controller' => 'costcenter', 'action' => 'edit_scc'],
    'cost_center/edit_sscc' => ['controller' => 'costcenter', 'action' => 'edit_sscc'],
    'usuarios' => ['controller' => 'user', 'action'=> 'index'],
    'usuarios/searchByDni' => ['controller' => 'user', 'action'=> 'searchByDni'],
    'usuarios/anticipoBuscarDni' => ['controller' => 'user', 'action'=> 'anticipoBuscarDni'],
    'anticipos' => ['controller' => 'anticipo', 'action' => 'index'],
    'anticipos/add' => ['controller' => 'anticipo', 'action' => 'add'],
    'anticipos/update' => ['controller' => 'anticipo', 'action' => 'update'],
    'anticipos/getAllScc' => ['controller' => 'anticipo', 'action' => 'getAllScc'],
    'anticipos/getSsccByScc' => ['controller' => 'anticipo', 'action' => 'getSsccByScc'],
    'anticipos/getSaldoDisponibleTiempoReal' => ['controller' => 'anticipo', 'action' => 'getSaldoDisponibleTiempoReal'],
    'anticipos/getAnticipoDetails' => ['controller' => 'anticipo', 'action' => 'getAnticipoDetails'],
    'anticipos/autorizar' => ['controller' => 'anticipo', 'action' => 'autorizar'],
    'anticipos/autorizarTotalmente' => ['controller' => 'anticipo', 'action' => 'autorizarTotalmente'],
    'anticipos/observarAnticipo' => ['controller' => 'anticipo', 'action' => 'observarAnticipo'],
    'anticipos/abonarAnticipo' => ['controller' => 'anticipo', 'action' => 'abonarAnticipo'],
    'tarifario/cargos' => ['controller' => 'tarifario', 'action' => 'obtenerCargos'], //ruta para obtener los cargos del tarifario
    'tarifario/montosCargo' => ['controller' => 'tarifario', 'action' => 'obtenerMontosPorCargo'],
    'presupuestos' => ['controller' => 'presupuestosscc', 'action' => 'index'],
    'presupuestos/get_ccs' => ['controller' => 'presupuestosscc', 'action' => 'get_ccs'],
    'presupuestos/get_sccs' => ['controller' => 'presupuestosscc', 'action' => 'get_sccs'],
    'presupuestos/get_ssccs' => ['controller' => 'presupuestosscc', 'action' => 'get_ssccs'],
    'presupuestos/add' => ['controller' => 'presupuestosscc', 'action' => 'add'],
    'presupuesto_sscc/add_funds' => ['controller' => 'presupuestosscc', 'action' => 'add_funds'],
    'rendiciones' => ['controller' => 'rendiciones', 'action' => 'index'],
    'rendiciones/getRendicionDetails' => ['controller' => 'rendiciones', 'action' => 'getRendicionDetails'],
    'rendiciones/getDetallesComprasMenores' => ['controller' => 'rendiciones', 'action' => 'getDetallesComprasMenores'],
    'rendiciones/getDetallesRendidosByRendicion' => ['controller' => 'rendiciones', 'action' => 'getDetallesRendidosByRendicion'],
    'rendiciones/guardarItemRendido' => ['controller' => 'rendiciones', 'action' => 'guardarItemRendido']
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