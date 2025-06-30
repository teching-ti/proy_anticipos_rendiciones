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
            $query = "SELECT a.id, a.departamento, a.solicitante_nombres, a.departamento_nombre, a.codigo_sscc, a.solicitante, s.nombre AS sscc_nombre, a.fecha_solicitud, 
                             a.monto_total_solicitado, 
                             h.id_usuario AS historial_usuario_id, h.estado as estado, u.nombre_usuario AS historial_usuario_nombre,
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

            $query .= " ORDER BY a.id DESC";
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

    // Obtener sub-subcentros de costo
    public function getAllSscc() {
        try {
            $query = "SELECT codigo, nombre FROM tb_sscc WHERE AND activo = 1 ORDER BY nombre";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Obtener sub-subcentros de costo filtrados por codigo_scc
    public function getSsccByScc($codigo_scc) {
        try {
            $query = "SELECT t.codigo, t.nombre 
                    FROM tb_sscc t 
                    INNER JOIN tb_presupuestos_sscc p ON t.codigo = p.codigo_sscc 
                    WHERE t.scc_codigo = :codigo_scc AND t.activo = 1 
                    ORDER BY t.nombre";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['codigo_scc' => $codigo_scc]);
            return $stmt->fetchAll(mode: PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getSsccByScc: " . $e->getMessage());
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

    // Verificar si un id solicitante ya existe y está pendiente
    public function anticipoPendiente($id_solicitante) {
        try {
            $query = "SELECT ha.estado
                FROM tb_anticipos a
                JOIN tb_historial_anticipos ha ON ha.id_anticipo = a.id
                WHERE a.id_usuario = :id_solicitante 
                AND a.fecha_solicitud = (
                    SELECT MAX(fecha_solicitud)
                    FROM tb_anticipos
                    WHERE id_usuario = a.id_usuario
                )
                AND ha.fecha = (
                    SELECT MAX(fecha)
                    FROM tb_historial_anticipos
                    WHERE id_anticipo = a.id
                )
                LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id_solicitante' => $id_solicitante]);
            $estado = $stmt->fetchColumn();
            return $estado != false && $estado != 'Finalizado';
        } catch (PDOException $e) {
            return false;
        }
    }

    // Agregar un nuevo anticipo y registrar en historial
    public function addAnticipo($id_usuario, $solicitante, $solicitante_nombres, $dni_solicitante, $departamento, $departamento_nombre,$codigo_sscc, $cargo, $nombre_proyecto, $fecha_solicitud, $motivo_anticipo, $monto_total_solicitado, $id_cat_documento, $detalles_gastos = [], $detalles_viajes) {
        try {
            $this->db->beginTransaction();

            // Insertar anticipo
            $query = "INSERT INTO tb_anticipos (id_usuario, solicitante, solicitante_nombres, dni_solicitante, departamento, departamento_nombre, codigo_sscc, cargo, nombre_proyecto, fecha_solicitud, motivo_anticipo, monto_total_solicitado, id_cat_documento)
                      VALUES (:id_usuario, :solicitante, :solicitante_nombres, :dni_solicitante, :departamento, :departamento_nombre, :codigo_sscc, :cargo, :nombre_proyecto, :fecha_solicitud, :motivo_anticipo, :monto_total_solicitado, :id_cat_documento)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id_usuario' => $id_usuario,
                'solicitante' => $solicitante,
                'solicitante_nombres' => $solicitante_nombres,
                'dni_solicitante' => $dni_solicitante,
                'departamento' => $departamento,
                'departamento_nombre' => $departamento_nombre,
                'codigo_sscc' => $codigo_sscc,
                'cargo' => $cargo,
                'nombre_proyecto' => $nombre_proyecto,
                'fecha_solicitud' => $fecha_solicitud,
                'motivo_anticipo' => $motivo_anticipo,
                'monto_total_solicitado' => $monto_total_solicitado,
                'id_cat_documento' => $id_cat_documento
            ]);

            // Obtener el ID del anticipo insertado
            $id_anticipo = $this->db->lastInsertId();

            
            // Obtener el ID del presupuesto
            $id_presupuesto = $this->getPresupuestoIdBySscc($codigo_sscc);
            if ($id_presupuesto === null) {
                error_log('No se encontró presupuesto para codigo_sscc: ' . $codigo_sscc);
                $this->db->rollBack();
                return false;
            }

            // Insertar movimiento en tb_movimientos_presupuesto
            $query = "INSERT INTO tb_movimientos_presupuesto (id_presupuesto, tipo_movimiento, monto, id_anticipo, id_usuario, fecha, comentario)
                      VALUES (:id_presupuesto, :tipo_movimiento, :monto, :id_anticipo, :id_usuario, NOW(), :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id_presupuesto' => $id_presupuesto,
                'tipo_movimiento' => 1, // Anticipo
                'monto' => $monto_total_solicitado,
                'id_anticipo' => $id_anticipo,
                'id_usuario' => $id_usuario,
                'comentario' => 'Movimiento por anticipo'
            ]);

            // Actualizar saldo_disponible en tb_presupuestos_sscc
            $query = "UPDATE tb_presupuestos_sscc 
                      SET saldo_disponible = saldo_disponible - :monto, 
                          ultima_actualizacion = NOW() 
                      WHERE id = :id_presupuesto AND activo = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'monto' => $monto_total_solicitado,
                'id_presupuesto' => $id_presupuesto
            ]);

            // Verificar que se haya actualizado al menos una fila
            if ($stmt->rowCount() === 0) {
                error_log('No se actualizó el saldo para id_presupuesto: ' . $id_presupuesto);
                $this->db->rollBack();
                return false;
            }

            // Insertar detalles de compras menores
            if (!empty($detalles_gastos)) {
                $query = "INSERT INTO tb_detalles_compras_menores (id_anticipo, descripcion, motivo, moneda, importe)
                          VALUES (:id_anticipo, :descripcion, :motivo, :moneda, :importe)";
                $stmt = $this->db->prepare($query);
                foreach ($detalles_gastos as $detalle) {
                    $stmt->execute([
                        'id_anticipo' => $id_anticipo,
                        'descripcion' => $detalle['descripcion'],
                        'motivo' => $detalle['motivo'],
                        'moneda' => $detalle['moneda'],
                        'importe' => $detalle['importe']
                    ]);
                }
            }else {
                error_log("No se recibieron detalles_gastos para insertar.");
            }

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

            // Insertar detalles de viajes
            if (!empty($detalles_viajes)) {
                error_log("Insertando " . count($detalles_viajes) . " personas de viaje: " . json_encode($detalles_viajes));
                $query_persona = "INSERT INTO tb_viajes_personas (id_anticipo, doc_identidad, nombre_persona, id_cargo)
                                  VALUES (:id_anticipo, :doc_identidad, :nombre_persona, :id_cargo)";
                $stmt_persona = $this->db->prepare($query_persona);

                $query_detalle = "INSERT INTO tb_detalles_viajes (id_viaje_persona, id_concepto, dias, monto, moneda)
                                  VALUES (:id_viaje_persona, :id_concepto, :dias, :monto, :moneda)";
                $stmt_detalle = $this->db->prepare($query_detalle);

                $query_transporte = "INSERT INTO tb_transporte_provincial (id_viaje_persona, tipo_transporte, ciudad_origen, ciudad_destino, fecha, monto, moneda)
                                     VALUES (:id_viaje_persona, :tipo_transporte, :ciudad_origen, :ciudad_destino, :fecha, :monto, :moneda)";
                $stmt_transporte = $this->db->prepare($query_transporte);

                foreach ($detalles_viajes as $index => $persona) {
                    // Insertar persona
                    try {
                        $result = $stmt_persona->execute([
                            'id_anticipo' => $id_anticipo,
                            'doc_identidad' => $persona['doc_identidad'],
                            'nombre_persona' => $persona['nombre_persona'],
                            'id_cargo' => $persona['id_cargo'],
                        ]);
                        if (!$result) {
                            error_log("Fallo al insertar persona de viaje #$index: No se insertó ninguna fila");
                            $this->db->rollBack();
                            return false;
                        }
                        $id_viaje_persona = $this->db->lastInsertId();
                    } catch (PDOException $e) {
                        error_log("Error al insertar persona de viaje #$index: " . $e->getMessage());
                        $this->db->rollBack();
                        return false;
                    }

                    // Insertar detalles de viáticos (hospedaje, movilidad, alimentación)
                    foreach ($persona['viaticos'] as $viatico) {
                        error_log("Los datos del viatico:");
                        error_log($id_viaje_persona);
                        error_log($viatico['dias']);
                        error_log($viatico['id_concepto']);
                        error_log($viatico['monto']);
                        if ($viatico['dias'] > 0) {
                            try {
                                $result = $stmt_detalle->execute([
                                    'id_viaje_persona' => $id_viaje_persona,
                                    'id_concepto' => $viatico['id_concepto'],
                                    'dias' => $viatico['dias'],
                                    'monto' => $viatico['monto'],
                                    'moneda' => 'pen'
                                ]);
                                if ($result) {
                                    error_log("Viático (concepto ID {$viatico['id_concepto']}) para persona #$index insertado: " . json_encode($viatico));
                                } else {
                                    error_log("Fallo al insertar viático para persona #$index");
                                    $this->db->rollBack();
                                    return false;
                                }
                            } catch (PDOException $e) {
                                error_log("Error al insertar viático para persona #$index: " . $e->getMessage());
                                $this->db->rollBack();
                                return false;
                            }
                        }
                    }

                    // Insertar detalles de transporte
                    foreach ($persona['transporte'] as $subindex => $transp) {
                        try {
                            $result = $stmt_transporte->execute([
                                'id_viaje_persona' => $id_viaje_persona,
                                'tipo_transporte' => $transp['tipo_transporte'],
                                'ciudad_origen' => $transp['ciudad_origen'],
                                'ciudad_destino' => $transp['ciudad_destino'],
                                'fecha' => $transp['fecha'],
                                'monto' => $transp['monto'],
                                'moneda' => 'pen'
                            ]);
                            if ($result) {
                                error_log("Transporte #$subindex para persona #$index insertado: " . json_encode($transp));
                            } else {
                                error_log("Fallo al insertar transporte #$subindex para persona #$index");
                                $this->db->rollBack();
                                return false;
                            }
                        } catch (PDOException $e) {
                            error_log("Error al insertar transporte #$subindex para persona #$index: " . $e->getMessage());
                            $this->db->rollBack();
                            return false;
                        }
                    }
                }
            } else {
                error_log("No se recibieron detalles_viajes para insertar");
            }

            $this->db->commit();
            error_log('Anticipo y movimiento registrados con éxito, saldo actualizado');
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error al agregar anticipo: ' . $e->getMessage());
            return false;
        }
    }

    public function getConceptosViaticos() {
        $query = "SELECT id, nombre FROM tb_categorias_tarifario WHERE activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cargoExists($id_cargo) {
        $query = "SELECT COUNT(*) FROM tb_cargos_tarifario WHERE id = :id_cargo";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id_cargo' => $id_cargo]);
        return $stmt->fetchColumn() > 0;
    }

    public function getTarifaByCargoAndConcepto($id_cargo, $id_concepto) {
        $query = "SELECT monto FROM tb_tarifario WHERE cargo_id = :cargo_id AND concepto_id = :concepto_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['cargo_id' => $id_cargo, 'concepto_id' => $id_concepto]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (float)$result['monto'] : null;
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

    public function getSaldoDisponibleBySscc($codigo_sscc) {
        try {
            $query = "SELECT saldo_disponible 
                      FROM tb_presupuestos_sscc 
                      WHERE codigo_sscc = :codigo_sscc AND activo = 1 
                      LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['codigo_sscc' => $codigo_sscc]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Consultando saldo para codigo_sscc ' . $codigo_sscc . ': ' . ($result ? $result['saldo_disponible'] : 'No encontrado'));
            return $result ? (float)$result['saldo_disponible'] : null;
        } catch (PDOException $e) {
            error_log('Error al obtener saldo disponible: ' . $e->getMessage());
            return null;
        }
    }

    public function getPresupuestoIdBySscc($codigo_sscc) {
        try {
            $query = "SELECT id 
                      FROM tb_presupuestos_sscc 
                      WHERE codigo_sscc = :codigo_sscc AND activo = 1 
                      LIMIT 1 FOR UPDATE";// se agregó el for update, bloqueo pesimista. Se pretende evitar concurrencia
            $stmt = $this->db->prepare($query);
            $stmt->execute(['codigo_sscc' => $codigo_sscc]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log('Consultando id_presupuesto para codigo_sscc ' . $codigo_sscc . ': ' . ($result ? $result['id'] : 'No encontrado'));
            return $result ? (int)$result['id'] : null;
        } catch (PDOException $e) {
            error_log('Error al obtener id_presupuesto: ' . $e->getMessage());
            return null;
        }
    }


    /*Cargar anticipos here*/
    // Obtener detalles completos de un anticipo por ID
    public function getAnticipoById($id_anticipo) {
        try {
            // Obtener datos principales del anticipo
            $query = "SELECT a.id, a.id_usuario, a.solicitante_nombres, a.dni_solicitante, a.departamento,
                        a.departamento_nombre, a.codigo_sscc, a.cargo, a.nombre_proyecto, a.fecha_solicitud, a.motivo_anticipo,
                        a.monto_total_solicitado, s.scc_codigo
                      FROM tb_anticipos a
                      LEFT JOIN tb_sscc s ON a.codigo_sscc = s.codigo
                      WHERE a.id = :id_anticipo";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id_anticipo' => $id_anticipo]);
            $anticipo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anticipo) {
                return null;
            }

            // Obtener detalles de compras menores
            $query_compras = "SELECT descripcion, motivo, moneda, importe 
                              FROM tb_detalles_compras_menores 
                              WHERE id_anticipo = :id_anticipo";
            $stmt_compras = $this->db->prepare($query_compras);
            $stmt_compras->execute(['id_anticipo' => $id_anticipo]);
            $anticipo['detalles_gastos'] = $stmt_compras->fetchAll(PDO::FETCH_ASSOC);



            //here iniicia
            // Obtener detalles de personas (viáticos y transporte)
            $query_personas = "SELECT vp.id, vp.doc_identidad, vp.nombre_persona, vp.id_cargo, c.nombre AS cargo_nombre
                               FROM tb_viajes_personas vp
                               LEFT JOIN tb_cargos_tarifario c ON vp.id_cargo = c.id
                               WHERE vp.id_anticipo = :id_anticipo";
            $stmt_personas = $this->db->prepare($query_personas);
            $stmt_personas->execute(['id_anticipo' => $id_anticipo]);
            $personas = $stmt_personas->fetchAll(PDO::FETCH_ASSOC);

            $anticipo['detalles_viajes'] = [];
            foreach ($personas as $persona) {
                $id_viaje_persona = $persona['id'];
                $persona_data = [
                    'doc_identidad' => $persona['doc_identidad'],
                    'nombre_persona' => $persona['nombre_persona'],
                    'id_cargo' => $persona['id_cargo'],
                    'cargo_nombre' => $persona['cargo_nombre'],
                    'viaticos' => [],
                    'transporte' => []
                ];

                // Obtener viáticos
                $query_viaticos = "SELECT dv.id_concepto, c.nombre AS concepto_nombre, dv.dias, dv.monto, dv.moneda
                                   FROM tb_detalles_viajes dv
                                   LEFT JOIN tb_categorias_tarifario c ON dv.id_concepto = c.id
                                   WHERE dv.id_viaje_persona = :id_viaje_persona";
                $stmt_viaticos = $this->db->prepare($query_viaticos);
                $stmt_viaticos->execute(['id_viaje_persona' => $id_viaje_persona]);
                $persona_data['viaticos'] = $stmt_viaticos->fetchAll(PDO::FETCH_ASSOC);

                // Obtener transporte
                $query_transporte = "SELECT tipo_transporte, ciudad_origen, ciudad_destino, fecha, monto, moneda
                                     FROM tb_transporte_provincial
                                     WHERE id_viaje_persona = :id_viaje_persona";
                $stmt_transporte = $this->db->prepare($query_transporte);
                $stmt_transporte->execute(['id_viaje_persona' => $id_viaje_persona]);
                $persona_data['transporte'] = $stmt_transporte->fetchAll(PDO::FETCH_ASSOC);

                $anticipo['detalles_viajes'][] = $persona_data;
            }
            //here termina
            return $anticipo;
        } catch (PDOException $e) {
            error_log("Error en getAnticipoById: " . $e->getMessage());
            return null;
        }
    }
}
?>