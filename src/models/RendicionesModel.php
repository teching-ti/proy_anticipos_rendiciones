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
                             a.motivo_anticipo, a.monto_total_solicitado, s.nombre AS sscc_nombre, r.fecha_inicio, r.fecha_rendicion, r.monto_rendido, 
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

    /// ELEMENTOS PARA VIAJES Y TRANSPORTE
    // Nuevos métodos para viáticos
    public function getDetallesViajesByAnticipo($id_anticipo) {
        try {
            error_log("Buscando viáticos para id_anticipo: $id_anticipo");
            $query = "SELECT dv.id, ct.nombre AS descripcion, a.motivo_anticipo AS motivo, dv.moneda, dv.monto AS importe, dv.dias, vp.nombre_persona
                  FROM tb_detalles_viajes dv
                  JOIN tb_viajes_personas vp ON dv.id_viaje_persona = vp.id
                  JOIN tb_categorias_tarifario ct ON dv.id_concepto = ct.id
                  JOIN tb_anticipos a ON vp.id_anticipo = a.id
                  WHERE vp.id_anticipo = :id_anticipo AND vp.valido = 1 AND dv.monto > 0
                  AND UPPER(dv.moneda) = 'PEN'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id_anticipo' => $id_anticipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener detalles de viáticos: ' . $e->getMessage());
            return [];
        }
    }

    // Nuevos métodos para transportes
    public function getDetallesTransportesByAnticipo($id_anticipo) {
        try {
            $query = "SELECT tp.id, tp.tipo_transporte AS descripcion, a.motivo_anticipo AS motivo, tp.moneda, tp.monto AS importe, tp.fecha, tp.ciudad_origen, tp.ciudad_destino
                  FROM tb_transporte_provincial tp
                  JOIN tb_viajes_personas vp ON tp.id_viaje_persona = vp.id
                  JOIN tb_anticipos a ON vp.id_anticipo = a.id
                  WHERE vp.id_anticipo = :id_anticipo AND vp.valido = 1 AND tp.monto > 0
                  AND UPPER(tp.moneda) = 'PEN'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id_anticipo' => $id_anticipo]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error al obtener detalles de transportes: ' . $e->getMessage());
            return [];
        }
    }

    public function getMontoSolicitadoByAnticipo($id_anticipo) {
        try {
            $query = "SELECT monto_total_solicitado FROM tb_anticipos WHERE id = :id_anticipo";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id_anticipo' => $id_anticipo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['monto_total_solicitado'] : 0.00;
        } catch (PDOException $e) {
            error_log('Error al obtener monto solicitado: ' . $e->getMessage());
            return 0.00;
        }
    }

    public function getMontoTotalRendidoByRendicion($id_rendicion) {
        try {
            $query = "SELECT COALESCE(SUM(importe_total), 0) as monto_total 
                    FROM (
                        SELECT importe_total FROM tb_comprobantes_compras WHERE id_rendicion = :id_rendicion1
                        UNION ALL
                        SELECT importe_total FROM tb_comprobantes_viaticos WHERE id_rendicion = :id_rendicion2
                        UNION ALL
                        SELECT importe_total FROM tb_comprobantes_transportes WHERE id_rendicion = :id_rendicion3
                    ) AS combined";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id_rendicion1' => $id_rendicion, ':id_rendicion2' => $id_rendicion, ':id_rendicion3' => $id_rendicion]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['monto_total'] ?? 0.00;
        } catch (PDOException $e) {
            error_log('Error al obtener monto total rendido: ' . $e->getMessage());
            return 0.00;
        }
    }

    public function getLatestEstadoRendicion($id_rendicion) {
        try {
            $query = "SELECT estado FROM tb_historial_rendiciones WHERE id_rendicion = :id_rendicion ORDER BY fecha DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id_rendicion' => $id_rendicion]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['estado' => $result ? $result['estado'] : 'Nuevo'];
        } catch (PDOException $e) {
            error_log('Error al obtener estado más reciente: ' . $e->getMessage());
            return ['estado' => 'Nuevo'];
        }
    }

    public function aprobarRendicion($id_rendicion, $id_usuario) {
        try {
            $this->db->beginTransaction();
            error_log("Rendicion: $id_rendicion");
            error_log("Usuario: $id_usuario");

            // Calcular monto_rendido (asumiendo que existe un método)
            $montoRendido = $this->getMontoTotalRendidoByRendicion($id_rendicion);

            // Actualizar tb_rendiciones
            $queryUpdate = "UPDATE tb_rendiciones SET monto_rendido = :monto_rendido WHERE id = :id_rendicion";
            $stmtUpdate = $this->db->prepare($queryUpdate);
            $stmtUpdate->execute([':monto_rendido' => $montoRendido, ':id_rendicion' => $id_rendicion]);

            // Insertar en tb_historial_rendiciones
            $queryInsert = "INSERT INTO tb_historial_rendiciones (id_rendicion, estado, fecha, id_usuario, comentario) 
                            VALUES (:id_rendicion, 'Autorizado', NOW(), :id_usuario, 'Rendición Autorizada')";
            $stmtInsert = $this->db->prepare($queryInsert);
            $stmtInsert->execute([':id_rendicion' => $id_rendicion, ':id_usuario' => $id_usuario]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al autorizar rendición: ' . $e->getMessage());
            return false;
        }
    }

    public function observarRendicion($id_rendicion, $id_usuario, $comentario) {
        try {
            $this->db->beginTransaction();
            $query = "INSERT INTO tb_historial_rendiciones (id_rendicion, estado, fecha, id_usuario, comentario) 
                    VALUES (:id_rendicion, 'Observado', NOW(), :id_usuario, :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id_rendicion' => $id_rendicion,
                ':id_usuario' => $id_usuario,
                ':comentario' => $comentario
            ]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al observar rendición: ' . $e->getMessage());
            return false;
        }
    }

    public function corregirRendicion($id_rendicion, $id_usuario, $comentario) {
        try {
            $this->db->beginTransaction();
            $query = "INSERT INTO tb_historial_rendiciones (id_rendicion, estado, fecha, id_usuario, comentario) 
                    VALUES (:id_rendicion, 'Nuevo', NOW(), :id_usuario, :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id_rendicion' => $id_rendicion,
                ':id_usuario' => $id_usuario,
                ':comentario' => $comentario
            ]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al corregir rendición: ' . $e->getMessage());
            return false;
        }
    }

    public function cerrarRendicion($id_rendicion, $id_usuario, $comentario, $id_anticipo) {
        try {
            $this->db->beginTransaction();
            $query = "INSERT INTO tb_historial_rendiciones (id_rendicion, estado, fecha, id_usuario, comentario) 
                    VALUES (:id_rendicion, 'Rendido', NOW(), :id_usuario, :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id_rendicion' => $id_rendicion,
                ':id_usuario' => $id_usuario,
                ':comentario' => $comentario
            ]);

            $queryAnticipo = "INSERT INTO tb_historial_anticipos (id_anticipo, estado, fecha, id_usuario, comentario) 
                          VALUES (:id_anticipo, 'Rendido', NOW(), :id_usuario, :comentario)";
            $stmtAnticipo = $this->db->prepare($queryAnticipo);
            $stmtAnticipo->execute([
                ':id_anticipo' => $id_anticipo,
                ':id_usuario' => $id_usuario,
                ':comentario' => $comentario
            ]);

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al cerrar rendición: ' . $e->getMessage());
            return false;
        }
    }

    // Nuevo método para obtener el id_usuario del último autorizador
    public function getLastAuthorizerId($idRendicion) {
        try {
            $query = "SELECT id_usuario FROM tb_historial_rendiciones WHERE id_rendicion = :id AND estado = 'Autorizado' ORDER BY fecha DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $idRendicion]);
            $idAutorizador = $stmt->fetchColumn();
            //error_log("ID del último autorizador para anticipo $idAnticipo: " . ($idAutorizador ?: 'No encontrado'));
            return $idAutorizador ?: null;
        } catch (PDOException $e) {
            error_log('Error al obtener el último autorizador: ' . $e->getMessage());
            return null;
        }
    }
}