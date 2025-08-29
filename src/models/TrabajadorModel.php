<?php
require_once 'src/config/Database.php';

class TrabajadorModel {
    private $db;
    private $db_external;

    public function __construct() {
        $this->db_external = $this->connectExternal();

        $database = new Database();
        $this->db = $database->connect();
    }

    private function connectExternal() {
        try {
            $dsn = "mysql:host=localhost;dbname=db_sst_hsqe;charset=utf8mb4";
            $user = "root";
            $pass = "";
            $conn = new PDO($dsn, $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $conn;
        } catch (PDOException $e) {
            error_log('Error al conectar a base_datos_a: ' . $e->getMessage());
            return null;
        }
    }
    
    public function getTrabajadorByDni($dni) {
        try {
            $query = "SELECT u.apellidos, u.nombres, u.cargo, u.departamento, u.correo, d.nombre as departamento_nombre
                      FROM tb_trabajadores u LEFT JOIN tb_departamentos d ON u.departamento = d.id
                      WHERE u.id = :dni AND activo=1";
            $stmt = $this->db_external->prepare($query);
            $stmt->execute(['dni' => $dni]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            // este error es solo para pruebas y revisión, deberá de eliminarse
            if($result){
                //error_log("Trabajador encontrado para DNI $dni: ".json_encode($result));
                return $result;
            }else{
                //error_log("No se encontró trabajadora activo para DNI: $dni");
                return null;
            }
        } catch (PDOException $e) {
            error_log('Error al consultar tb_trabajadores: ' . $e->getMessage());
            return null;
        }
    }

    //Obtener datos por DNI para el formulario de registro
    public function findByDni($dni) {
        try {
            $query = "SELECT u.nombres, u.apellidos, u.cargo, u.departamento, u.correo, d.nombre as departamento_nombre
                      FROM tb_trabajadores u LEFT JOIN tb_departamentos d ON u.departamento = d.id
                      WHERE u.id = :dni LIMIT 1";
            $stmt = $this->db_external->prepare($query);
            $stmt->execute(['dni' => $dni]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Buscando DNI ' . $dni . ': ' . ($result ? 'Encontrado' : 'No encontrado'));
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('Error al buscar trabajador por DNI: ' . $e->getMessage());
            return null;
        }
    }

    // Obtener aprobadores (rol 2) activos con departamento específico
    public function getAprobadoresByDepartamento($departamento) {
        try {
            // Paso 1: Obtener los DNI de los aprobadores (rol = 2) desde tb_usuarios
            $query = "SELECT dni FROM tb_usuarios WHERE rol = 2";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $dnis = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (empty($dnis)) {
                error_log("No se encontraron aprobadores con rol 2");
                return [];
            }

            // Paso 2: Construir la consulta con parámetros con nombre para el IN
            $placeholders = [];
            $params = [];
            foreach ($dnis as $index => $dni) {
                $paramName = ":dni_$index";
                $placeholders[] = $paramName;
                $params[$paramName] = $dni;
            }
            $placeholdersStr = implode(',', $placeholders);
            $query = "SELECT correo FROM tb_trabajadores WHERE id IN ($placeholdersStr) AND activo = 1 AND departamento = :departamento";
            $params[':departamento'] = $departamento;

            $stmt = $this->db_external->prepare($query);
            $stmt->execute($params);
            $correos = $stmt->fetchAll(PDO::FETCH_COLUMN);

            error_log("Aprobadores encontrados para departamento $departamento: " . json_encode($correos));
            return $correos;
        } catch (PDOException $e) {
            error_log('Error al obtener aprobadores: ' . $e->getMessage());
            return [];
        }
    }

    
    // Nuevo método para obtener DNI de usuarios con el rol de tesorería
    public function getDnisByRol5() {
        try {
            $query = "SELECT dni FROM tb_usuarios WHERE rol = 5";
            $stmt = $this->db->query($query);
            $dnis = $stmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("DNI de usuarios con rol tesorero: " . json_encode($dnis));
            return $dnis;
        } catch (PDOException $e) {
            error_log('Error al obtener DNI de usuarios con rol tesorero: ' . $e->getMessage());
            return [];
        }
    }

    // Nuevo método para obtener DNI de usuarios con el rol de contrador
    public function getDnisByRol4() {
        try {
            $query = "SELECT dni FROM tb_usuarios WHERE rol = 4";
            $stmt = $this->db->query($query);
            $dnis = $stmt->fetchAll(PDO::FETCH_COLUMN);
            error_log("DNI de usuarios con rol contador: " . json_encode($dnis));
            return $dnis;
        } catch (PDOException $e) {
            error_log('Error al obtener DNI de usuarios con rol contrador: ' . $e->getMessage());
            return [];
        }
    }

    public function getDniById($id){
        try {
            $query = "SELECT dni FROM tb_usuarios WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetchColumn();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log('Error al obtener DNI de usuario con rol tesorero: ' . $e->getMessage());
            return [];
        }
    }

    // Funcionalidad para determinar si existe un gerente en el departamento, se utiliza para cuando se logue un usuario que no tiene cargo gerente
    public function existeGerenteEnDepartamento($departamento_id) {
        try {
            $query = "SELECT COUNT(*) as total 
                    FROM tb_trabajadores 
                    WHERE departamento = :dep 
                    AND cargo LIKE '%gerente%' 
                    AND activo = 1";
            $stmt = $this->db_external->prepare($query);
            $stmt->execute(['dep' => $departamento_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['total'] > 0;
        } catch (PDOException $e) {
            error_log('Error al verificar gerente en departamento: ' . $e->getMessage());
            return false;
        }
    }
}
?>