<?php
require_once 'src/config/Database.php';

class RendicionesModel {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Funcionalidad para actualizar el estado de una rendición
    public function updateEstado($id_rendicion, $estado, $id_usuario, $comentario = null) {
        try {
            $query = "INSERT INTO tb_historial_rendiciones (id_rendicion, estado, fecha, id_usuario, comentario)
                      VALUES (:id_rendicion, :estado, NOW(), :id_usuario, :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id_rendicion' => $id_rendicion,
                ':estado' => $estado,
                ':id_usuario' => $id_usuario,
                ':comentario' => $comentario ?? "Cambio a $estado"
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('Error al actualizar estado en historial de rendiciones: ' . $e->getMessage());
            return false;
        }
    }

    // Crear rendición
    public function createRendicion($id_anticipo, $id_usuario, $fecha_inicio, $fecha_rendicion, $id_cat_documento, $monto_rendido = 0, $comentario = null) {
        try {
            $this->db->beginTransaction();
            $query = "INSERT INTO tb_rendiciones (id_anticipo, id_usuario, fecha_inicio, fecha_rendicion, id_cat_documento, monto_rendido, comentario)
                      VALUES (:id_anticipo, :id_usuario, :fecha_inicio, :fecha_rendicion, :id_cat_documento, :monto_rendido, :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id_anticipo' => $id_anticipo,
                ':id_usuario' => $id_usuario,
                ':fecha_inicio' => $fecha_inicio,
                ':fecha_rendicion' => $fecha_rendicion,
                ':id_cat_documento' => $id_cat_documento,
                ':monto_rendido' => 0,
                ':comentario' => $comentario
            ]);
            $id_rendicion = $this->db->lastInsertId();
            $this->db->commit();
            return $id_rendicion;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al crear rendición: ' . $e->getMessage());
            return false;
        }
    }
    
    // Funcionalidad que obtendrá los registros de rendiciones
    public function getRendicionesByRole($user_id, $rol) {
        try {
            $query = "SELECT r.id, r.id_anticipo, a.departamento, a.solicitante_nombres, a.departamento_nombre, a.codigo_sscc, a.nombre_proyecto,
                             s.nombre AS sscc_nombre, r.fecha_inicio, r.fecha_rendicion, r.monto_rendido, 
                             h.estado AS estado, h.comentario AS comentario, u.nombre_usuario AS historial_usuario_nombre,
                             h.fecha AS historial_fecha, h.id_usuario AS historial_usuario_id
                      FROM tb_rendiciones r
                      LEFT JOIN tb_anticipos a ON r.id_anticipo = a.id
                      LEFT JOIN tb_sscc s ON a.codigo_sscc = s.codigo
                      LEFT JOIN (
                          SELECT id_rendicion, id_usuario, fecha, estado, comentario
                          FROM tb_historial_rendiciones
                          WHERE (id_rendicion, fecha) IN (
                              SELECT id_rendicion, MAX(fecha)
                              FROM tb_historial_rendiciones
                              GROUP BY id_rendicion
                          )
                      ) h ON r.id = h.id_rendicion
                      LEFT JOIN tb_usuarios u ON h.id_usuario = u.id
                      WHERE 1=1";
            $params = [];
            if ($rol == 2) { // Jefatura
                $query .= " AND a.departamento = :dep_id";
                $params['dep_id'] = $_SESSION['trabajador']['departamento'];
            } elseif ($rol == 3) { // Usuario normal
                $query .= " AND r.id_usuario = :user_id";
                $params['user_id'] = $user_id;
            } // Rol 4 ve todo, sin restricciones

            $query .= " ORDER BY r.id DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener rendiciones: ' . $e->getMessage());
            return [];
        }
    }

    // Función que se utilizará para cargar los detalles completos de una rendición por ID
    public function getRendicionById($id_rendicion){
        try{
            $query = "SELECT 
                r.id, 
                r.id_anticipo, 
                r.id_usuario,
                r.fecha_inicio,
                r.fecha_rendicion,
                r.monto_rendido,
                a.solicitante_nombres,
                a.dni_solicitante,
                a.codigo_sscc,
                a.nombre_proyecto,
                a.motivo_anticipo,
                a.cargo,
                a.departamento,
                a.departamento_nombre,
                s.scc_codigo
                FROM tb_rendiciones r
                LEFT JOIN
                tb_anticipos a ON r.id_usuario = a.id_usuario
                LEFT JOIN
                tb_sscc s ON a.codigo_sscc = s.codigo
                WHERE r.id = :id_rendicion";

            $stmt = $this->db->prepare(($query));
            $stmt->execute(['id_rendicion' => $id_rendicion]);
            $rendicion = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$rendicion){
                return null;
            }

            return $rendicion;
        } catch (PDOException $e){
            error_log("Error al usar getRendicionById: ". $e->getMessage());
            return null;
        }
    }

    // Función que se utilizará para obtener los detalles de compras menores asociadas a mi anticipo
    // Esto será importante para poder cumplir con los detalles de mis rendiciones
    public function getDetallesComprasMenoresByAnticipo($id_anticipo) {
        try {
            $query = "SELECT id, descripcion, motivo, moneda, importe 
                      FROM tb_detalles_compras_menores 
                      WHERE id_anticipo = :id_anticipo AND valido = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id_anticipo' => $id_anticipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener detalles de compras menores: ' . $e->getMessage());
            return [];
        }
    }
}