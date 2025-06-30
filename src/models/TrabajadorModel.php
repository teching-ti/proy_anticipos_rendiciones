<?php
class TrabajadorModel {
    private $db_external;

    public function __construct() {
        $this->db_external = $this->connectExternal();
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
                error_log("Trabajador encontrado para DNI $dni: ".json_encode($result));
                return $result;
            }else{
                error_log("No se encontró trabajadora activo para DNI: $dni");
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
}
?>