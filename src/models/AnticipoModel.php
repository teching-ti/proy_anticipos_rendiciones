<?php
require_once 'src/config/Database.php';

class AnticipoModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Obtener anticipos según el rol del usuario
    public function getAnticiposByRole($user_id, $rol) {
        try {
            $query = "SELECT a.id, a.area, a.codigo_sscc, a.solicitante, s.nombre AS sscc_nombre, a.fecha_solicitud, 
                             a.monto_total_solicitado, 
                             h.id_usuario AS historial_usuario_id, h.estado as estado,u.nombre_usuario AS historial_usuario_nombre,
                             h.fecha AS historial_fecha
                      FROM tb_anticipos a
                      LEFT JOIN tb_sscc s ON a.codigo_sscc = s.codigo
                      LEFT JOIN (
                          SELECT id_anticipo, id_usuario, fecha, estado
                          FROM tb_historial_anticipos
                          WHERE (id_anticipo, fecha) IN (
                              SELECT id_anticipo, MAX(fecha)
                              FROM tb_historial_anticipos
                              GROUP BY id_anticipo
                          )
                      ) h ON a.id = h.id_anticipo
                      LEFT JOIN tb_usuarios u ON h.id_usuario = u.id
                      WHERE 1=1";
            $params = [];
            if ($rol == 2) { // Jefatura
                $query .= " AND (a.id_usuario = :user_id OR a.jefe_aprobador = :jefe_id)";
                $params['user_id'] = $user_id;
                $params['jefe_id'] = $user_id;
            } elseif ($rol == 3) { // Usuario normal
                $query .= " AND a.id_usuario = :user_id";
                $params['user_id'] = $user_id;
            } // Rol 4 ve todo, sin restricciones

            //error_log("El id de usuario es ".$user_id. "y el rol es: ".$rol);
            //error_log("Consulta SQL: " . $query);
            //error_log("Parámetros: " . print_r($params, true));

            $query .= " ORDER BY a.fecha_solicitud DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener anticipos: ' . $e->getMessage());
            return [];
        }
    }

    // Obtener usuarios con rol Jefatura (rol = 2)
    public function getJefes() {
        try {
            $query = "SELECT id, nombre_usuario FROM tb_usuarios WHERE rol = 2 ORDER BY nombre_usuario";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Obtener sub-centros de costo para el formulario
    public function getAllScc() {
        try {
            $query = "SELECT codigo, nombre FROM tb_scc WHERE activo = 1 ORDER BY nombre";
            $stmt2 = $this->db->prepare($query);
            $stmt2->execute();
            return $stmt2->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Obtener sub-subcentros de costo para el formulario
    public function getAllSscc() {
        try {
            $query = "SELECT codigo, nombre FROM tb_sscc WHERE activo = 1 ORDER BY nombre";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Obtener ID del documento "Anticipo"
    public function getAnticipoDocumentoId() {
        try {
            $query = "SELECT id FROM tb_cat_documento WHERE nombre = 'Anticipo' LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error al obtener ID de documento Anticipo: ' . $e->getMessage());
            return false;
        }
    }

    // Verificar si un DNI solicitante ya existe
    public function dniSolicitanteExists($dni_solicitante) {
        try {
            $query = "SELECT COUNT(*) FROM tb_anticipos WHERE dni_solicitante = :dni_solicitante";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['dni_solicitante' => $dni_solicitante]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Agregar un nuevo anticipo y registrar en historial
    public function addAnticipo($id_usuario, $solicitante, $dni_solicitante, $area, $codigo_sscc, $cargo, $nombre_proyecto, $fecha_solicitud, $motivo_anticipo, $monto_total_solicitado, $jefe_aprobador, $id_cat_documento) {
        try {
            $this->db->beginTransaction();

            // Insertar anticipo
            $query = "INSERT INTO tb_anticipos (id_usuario, solicitante, dni_solicitante, area, codigo_sscc, cargo, nombre_proyecto, fecha_solicitud, motivo_anticipo, monto_total_solicitado, jefe_aprobador, id_cat_documento)
                      VALUES (:id_usuario, :solicitante, :dni_solicitante, :area, :codigo_sscc, :cargo, :nombre_proyecto, :fecha_solicitud, :motivo_anticipo, :monto_total_solicitado, :jefe_aprobador, :id_cat_documento)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id_usuario' => $id_usuario,
                'solicitante' => $solicitante,
                'dni_solicitante' => $dni_solicitante,
                'area' => $area,
                'codigo_sscc' => $codigo_sscc,
                'cargo' => $cargo,
                'nombre_proyecto' => $nombre_proyecto,
                'fecha_solicitud' => $fecha_solicitud,
                'motivo_anticipo' => $motivo_anticipo,
                'monto_total_solicitado' => $monto_total_solicitado,
                'jefe_aprobador' => $jefe_aprobador ?: null,
                'id_cat_documento' => $id_cat_documento
            ]);

            // Obtener el ID del anticipo insertado
            $id_anticipo = $this->db->lastInsertId();

            // Insertar en historial
            $query = "INSERT INTO tb_historial_anticipos (id_anticipo, estado, id_usuario, fecha, comentario)
                      VALUES (:id_anticipo, :estado, :id_usuario, NOW(), :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id_anticipo' => $id_anticipo,
                'estado' => 'Nuevo',
                'id_usuario' => $id_usuario,
                'comentario' => 'Anticipo creado'
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al agregar anticipo: ' . $e->getMessage());
            return false;
        }
    }

    // Actualizar estado de un anticipo y registrar en historial
    public function updateAnticipoEstado($id, $estado, $id_usuario, $comentario = null) {
        try {
            $this->db->beginTransaction();

            // Actualizar estado en tb_anticipos
            $query = "UPDATE tb_anticipos
                      SET jefe_aprobador = :jefe_aprobador
                      WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'jefe_aprobador' => $id_usuario,
                'id' => $id
            ]);

            // Insertar en historial
            $query = "INSERT INTO tb_historial_anticipos (id_anticipo, estado, id_usuario, fecha, comentario)
                      VALUES (:id_anticipo, :estado, :id_usuario, NOW(), :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id_anticipo' => $id,
                'estado' => $estado,
                'id_usuario' => $id_usuario,
                'comentario' => $comentario ?? "Cambio a $estado"
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al actualizar estado de anticipo: ' . $e->getMessage());
            return false;
        }
    }
}
?>