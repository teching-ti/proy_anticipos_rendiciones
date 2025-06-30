<?php
require_once 'src/config/Database.php';
require_once 'src/models/UserModel.php';
require_once 'src/models/TrabajadorModel.php';

class LoginController {
    private $db;
    private $userModel;
    private $trabajadorModel;

    /*Revisar los error_log, estos deberán mostrar información guía solo durante el desarrollo*/
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->userModel = new UserModel();
        $this->trabajadorModel = new TrabajadorModel();
        if (!$this->trabajadorModel) {
            error_log('Error: No se pudo inicializar TrabajadorModel');
        }
    }

    // vista a dirigirse tras completar el formulario de login
    public function index() {
        if (isset($_SESSION['dni'])) {
            header('Location: /proy_anticipos_rendiciones/dashboard');
            exit;
        }
        $error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
        unset($_SESSION['login_error']);
        require_once 'src/views/login.php';
    }

    // proceso del formulario de login tras hacer clic en el boton correspondiente
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre_usuario = trim($_POST['form-usuario'] ?? '');
            $contrasena = trim($_POST['form-contrasena'] ?? '');

            if (empty($nombre_usuario) || empty($contrasena)) {
                $_SESSION['login_error'] = 'Por favor, completa todos los campos.';
                header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
                exit;
            }

            $user = $this->userModel->getUserByUsernameAndPassword($nombre_usuario, $contrasena);
            if ($user) {

                //se revisa que el trabajador exista en base al dni registrado
                $trabajador = $this->trabajadorModel->getTrabajadorByDni($user['dni']);

                // en caso de haber validad ocorrectamente, se guardará en una sesión los datos obtenidos
                if ($trabajador) {
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['dni'] = $user['dni'];
                    $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
                    $_SESSION['rol'] = $user['rol'];
                    $_SESSION['rol_nombre'] = $user['rol_nombre'];
                    /*únicamente para mostrarlos*/
                    $_SESSION['trabajador'] = $trabajador; // Almacenar apellidos, nombre, cargo, departamento, correo ?-
                    /*Revisar error_log*/
                    error_log("Datos de trabajador cargados de forma exitosa: " . json_encode($trabajador));
                    
                    header('Location: /proy_anticipos_rendiciones/dashboard');
                    exit;
                } else {
                    $_SESSION['login_error'] = 'Error. No se pudo ingresar al sistema.';
                    error_log("Login fallido para $nombre_usuario: DNI {$user['dni']} no es trabajador activo");
                    header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
                    exit;
                }

                header('Location: /proy_anticipos_rendiciones/dashboard');
                exit;
            } else {
                $_SESSION['login_error'] = 'Nombre de usuario o contraseña incorrectos.';
                header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
                exit;
            }
        }
        header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
        exit;
    }

    // Cerrar sesión
    public function logout() {
        session_destroy();
        header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
        exit;
    }
}