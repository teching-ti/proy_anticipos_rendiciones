<?php
require_once 'src/config/Database.php';

class UserModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();

        // se usa conexión a la base de datos de trabajadores
        try {
            $this->db_trabajadores = new PDO(
                'mysql:host=localhost;dbname=db_sst_hsqe;charset=utf8',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            error_log('Error al conectar con db_trabajadores: ' . $e->getMessage());
            $this->db_trabajadores = null;
        }
    }

    // Validar usuario por nombre_usuario y contraseña
    public function getUserByUsernameAndPassword($nombre_usuario, $password) {
        try {
            $query = "SELECT u.id, u.nombre_usuario, u.dni, u.contrasena, u.rol, r.nombre AS rol_nombre
                      FROM tb_usuarios u
                      JOIN tb_roles r ON u.rol = r.id
                      WHERE u.nombre_usuario = :nombre_usuario";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['nombre_usuario' => $nombre_usuario]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // registro para el log
            $logUser = $user ? [
                'id' => $user['id'],
                'nombre_usuario' => $user['nombre_usuario'],
                'dni' => $user['dni'],
                'rol' => $user['rol'],
                'rol_nombre' => $user['rol_nombre']
            ] : null;

            error_log("Usuario encontrado: " . ($logUser ? json_encode($logUser) : 'Ninguno'));

            if ($user && password_verify($password, $user['contrasena'])) {
                error_log("Contraseña verificada para $nombre_usuario");
                // Eliminar la contraseña del resultado por seguridad
                unset($user['contrasena']);
                return $user;
            }
            error_log("Fallo en autenticación para $nombre_usuario");
            return null;
        } catch (PDOException $e) {
            error_log('Error al validar usuario: ' . $e->getMessage());
            return null;
        }
    }

    // Obtener todos los roles para el desplegable
    public function getAllRoles() {
        try {
            $query = "SELECT id, nombre FROM tb_roles WHERE nombre!='Administrador' ORDER BY nombre DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Verificar si un DNI ya existe
    public function dniExists($dni) {
        try {
            $query = "SELECT COUNT(*) FROM tb_usuarios WHERE dni = :dni";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['dni' => $dni]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Agregar un nuevo usuario
    public function addUser($nombre_usuario, $contrasena, $dni, $rol,$n_cuenta) {

        try {
            $query = "INSERT INTO tb_usuarios (nombre_usuario, contrasena, dni, rol, n_cuenta)
                      VALUES (:nombre_usuario, :contrasena, :dni, :rol, :n_cuenta)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'nombre_usuario' => $nombre_usuario,
                'contrasena' => password_hash($contrasena, PASSWORD_DEFAULT),
                'dni' => $dni,
                'rol' => $rol,
                'n_cuenta' => $n_cuenta
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Obtener listado de usuarios con estado
    public function getUsersData() {
        try {
            // Obtener usuarios de tb_usuarios con el nombre del rol
            $query = "SELECT u.id, u.nombre_usuario, u.dni, u.rol, u.n_cuenta, r.nombre AS rol_nombre
                      FROM tb_usuarios u
                      JOIN tb_roles r ON u.rol = r.id";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Añadir estado basado en tb_trabajadores, se trabaja con la base de datos de hsqe
            foreach ($users as &$user) {
                $user['estado'] = 'Sin datos'; // Valor por defecto
                if ($this->db_trabajadores) {
                    $query = "SELECT activo, nombres, apellidos FROM tb_trabajadores WHERE id = :id";
                    $stmt = $this->db_trabajadores->prepare($query);
                    $stmt->execute(['id' => $user['dni']]);
                    $trabajador = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($trabajador) {
                        $user['estado'] = $trabajador['activo'] == 1 ? 'Apto' : 'Cesado';
                        $user['nombres-completos'] = $trabajador['nombres'] . " " . $trabajador['apellidos'];
                    }
                }
            }
            unset($user); // Romper la referencia

            return $users;
        } catch (PDOException $e) {
            error_log('Error al obtener datos de todos los usuarios: ' . $e->getMessage());
            return [];
        }
    }

    public function getNumCuenta($dni){
        try {
            $query = "SELECT n_cuenta FROM tb_usuarios WHERE dni = :dni";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':dni' => $dni]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['n_cuenta'] : null;
        } catch (PDOException $e) {
            error_log('Error al obtener número de cuenta: ' . $e->getMessage());
            return null;
        }
    }    
}