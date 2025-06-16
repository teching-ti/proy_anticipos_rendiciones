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
$request_uri = trim($_SERVER['REQUEST_URI'], '/');
$base_path = 'proy_anticipos_rendiciones';
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
    'anticipos' => ['controller' => 'anticipo', 'action' => 'index'],
    'anticipos/add' => ['controller' => 'anticipo', 'action' => 'add'],
    'anticipos/approve' => ['controller' => 'anticipo', 'action' => 'approve'],
    'anticipos/reject' => ['controller' => 'anticipo', 'action' => 'reject']
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