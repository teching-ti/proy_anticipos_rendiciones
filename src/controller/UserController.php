<?php
require_once 'src/config/Database.php';
require_once 'src/models/UserModel.php';
require_once 'src/models/TrabajadorModel.php';

class UserController {
    private $db;
    private $userModel;
    private $trabajadorModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->userModel = new UserModel();
        $this->trabajadorModel = new TrabajadorModel();
        if (!$this->trabajadorModel) {
            error_log('Error: No se pudo inicializar TrabajadorModel');
        }
    }

    public function index(){
        $roles = $this->userModel->getAllRoles();
        // if ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 4) {
        //     header('Location: iniciar_sesion');
        //     exit;
        // }
        $users_data = $this->userModel->getUsersData();
        require_once 'src/views/users.php';
    }

    public function generarContrasena($username) {
        if (empty($username) || strlen($username) < 2) {
            return "Nombre de usuario inválido";
        }

        $primeraLetra = $username[0];
        $resto = substr($username, 1);
        $numeroAleatorio = rand(100, 999);

        return $primeraLetra . '*' . $resto . '+-' . $numeroAleatorio;
    }
    
    // Mostrar el formulario de agregar usuario
    public function add() {
        // Solo permitir acceso a administradores (por ejemplo)
        // if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1 && $_SESSION['rol'] != 4) { // 1 = Administrador
        //     header('Location: iniciar_sesion');
        //     exit;
        // }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre_usuario = trim($_POST['user-nombre']);

            $primeraLetra = $nombre_usuario[0];
            $resto = substr($nombre_usuario, 1);
            $numeroAleatorio = rand(1000, 9999);
            $contrasena = '@'.strtoupper($primeraLetra).'*' .$resto.'+-'.$numeroAleatorio;

            $dni = trim($_POST['doc-identidad']);
            $rol = (int)($_POST['user-rol']);
            $n_cuenta = trim($_POST['user-cuenta']);
            error_log("Datos de usuario a registrar: $nombre_usuario, $contrasena, $dni, $rol, $n_cuenta");

            // Validaciones
            if (empty($nombre_usuario) || empty($contrasena) || empty($dni)) {
                // $error = 'Todos los campos son obligatorios.';
                // error_log($error);
                $_SESSION['error'] = 'Todos los campos son obligatorios.';
            } elseif (!preg_match('/^\d+$/', $dni)) {
                // $error = 'El documento debe ser únicamente un número.';
                // error_log($error);
                $_SESSION['error'] = 'El documento debe ser únicamente un número';
            }elseif ($this->userModel->dniExists($dni)) {
                // $error = 'Un usuario con este número de DNI ya existe.';
                // error_log($error);
                $_SESSION['error'] = 'Ya existe un usuario registrado con este número de documento.';
            } else {
                // Agregar usuario
                if ($this->userModel->addUser($nombre_usuario, $contrasena, $dni, $rol, $n_cuenta)) {
                    // $success = 'Usuario registrado correctamente.';
                    // error_log($success);
                    $_SESSION['success'] = "Usuario registrado correctamente:   $nombre_usuario, $contrasena";
                } else {
                    // $error = 'Error al registrar el usuario.';
                    // error_log($error);
                    $_SESSION['error'] = 'El usuario no pudo ser registrado.';
                }
                
            }
        }
        header('Location: usuarios');
        exit;
    }

    public function getNumCuenta(){
        if (!isset($_GET['dni'])) {
            http_response_code(400);
            echo json_encode(['error' => 'DNI no proporcionado']);
            return;
        }

        $dni = trim($_GET['dni']);
        $n_cuenta = $this->userModel->getNumCuenta($dni);

        if ($n_cuenta === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Número de cuenta no encontrado']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode(['n_cuenta' => $n_cuenta]);
    }

    // Buscar trabajador por DNI (AJAX), se utiliza para completar los campos del formulario de crear usuario
    public function searchByDni() {
        // session_start();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $dni = trim($_POST['doc-identidad'] ?? '');
        error_log('Buscando DNI en controlador: ' . $dni);

        $trabajador = $this->trabajadorModel->findByDni($dni);
        if ($trabajador) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'nombres' => $trabajador['nombres'],
                    'apellidos' => $trabajador['apellidos'],
                    'cargo' => $trabajador['cargo'],
                    'departamento' => $trabajador['departamento'],
                    'departamento_nombre' => $trabajador['departamento_nombre'],
                    'correo' => $trabajador['correo']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Trabajador no encontrado']);
        }
        exit;
    }

    // Buscar trabajador por DNI (AJAX), se utiliza para completar los campos del formulario de crear usuario
    public function anticipoBuscarDni() {
        header('Content-Type: application/json');

        if (!isset($_GET['doc-identidad'])) {
            http_response_code(400);
            echo json_encode(['error' => 'cargo_id no proporcionado']);
            return;
        }

        $dni = trim($_GET['doc-identidad'] ?? '');
        error_log('Buscando DNI en controlador: ' . $dni);

        error_log($dni);
        $trabajador = $this->trabajadorModel->findByDni($dni);
        if ($trabajador) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'nombres' => $trabajador['nombres'],
                    'apellidos' => $trabajador['apellidos']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Trabajador no encontrado']);
        }
        exit;
    }
}