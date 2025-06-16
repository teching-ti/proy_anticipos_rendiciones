<?php
require_once 'src/config/Database.php';
require_once 'src/models/UserModel.php';

class UserController {
    private $db;
    private $userModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->userModel = new UserModel();
    }

    public function index(){
        if ($_SESSION['rol'] != 1) {
            header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
            exit;
        }
        $users_data = $this->userModel->getUsersData();
        require_once 'src/views/users.php';
    }

    // Mostrar el formulario de agregar usuario
    public function add() {
        // Solo permitir acceso a administradores (por ejemplo)
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) { // 1 = Administrador
            header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
            exit;
        }
        
        $roles = $this->userModel->getAllRoles();
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
            $contrasena = trim($_POST['contrasena'] ?? '');
            $dni = trim($_POST['dni'] ?? '');
            $rol = (int)($_POST['rol'] ?? 0);

            // Validaciones
            if (empty($nombre_usuario) || empty($contrasena) || empty($dni) || $rol === 0) {
                $error = 'Todos los campos son obligatorios.';
            } elseif (!preg_match('/^[0-9]{8}$/', $dni)) {
                $error = 'El DNI debe tener 8 dígitos.';
            } elseif ($this->userModel->dniExists($dni)) {
                $error = 'El DNI ya está registrado.';
            } elseif (strlen($contrasena) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } else {
                // Verificar que el rol exista
                $valid_role = false;
                foreach ($roles as $r) {
                    if ($r['id'] === $rol) {
                        $valid_role = true;
                        break;
                    }
                }
                if (!$valid_role) {
                    $error = 'Rol inválido.';
                } else {
                    // Agregar usuario
                    if ($this->userModel->addUser($nombre_usuario, $contrasena, $dni, $rol)) {
                        $success = 'Usuario registrado correctamente.';
                    } else {
                        $error = 'Error al registrar el usuario.';
                    }
                }
            }
        }
        require_once 'src/views/add_user.php';
    }
}