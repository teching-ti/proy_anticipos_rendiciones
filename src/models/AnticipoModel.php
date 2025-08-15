<?php
require_once 'src/config/Database.php';
require_once 'src/models/RendicionesModel.php';

date_default_timezone_set('America/Lima');

class AnticipoModel {
    private $db;
    private $rendicionesModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->rendicionesModel = new RendicionesModel();
    }

    // Obtener anticipos según el rol del usuario
    public function getAnticiposByRole($user_id, $rol) {
        try {
            $query = "SELECT a.id, a.departamento, a.solicitante_nombres, a.departamento_nombre, a.codigo_sscc, a.solicitante, a.motivo_anticipo, s.nombre AS sscc_nombre, a.fecha_solicitud, 
                             a.monto_total_solicitado, 
                             h.id_usuario AS historial_usuario_id, h.estado as estado, h.comentario as comentario, u.nombre_usuario AS historial_usuario_nombre,
                             h.fecha AS historial_fecha
                      FROM tb_anticipos a
                      LEFT JOIN tb_sscc s ON a.codigo_sscc = s.codigo
                      LEFT JOIN (
                          SELECT id_anticipo, id_usuario, fecha, estado, comentario
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
                $query .= " AND a.departamento = :dep_id";
                $params['dep_id'] = $_SESSION['trabajador']['departamento'];
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
            return $estado != false && $estado != 'Rendido';
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
                $query_persona = "INSERT INTO tb_viajes_personas (id_anticipo, doc_identidad, nombre_persona, id_cargo, valido)
                                  VALUES (:id_anticipo, :doc_identidad, :nombre_persona, :id_cargo, :valido)";
                $stmt_persona = $this->db->prepare($query_persona);

                $query_detalle = "INSERT INTO tb_detalles_viajes (id_viaje_persona, id_concepto, dias, monto, moneda)
                                  VALUES (:id_viaje_persona, :id_concepto, :dias, :monto, :moneda)";
                $stmt_detalle = $this->db->prepare($query_detalle);

                $query_transporte = "INSERT INTO tb_transporte_provincial (id_viaje_persona, tipo_transporte, ciudad_origen, ciudad_destino, fecha, monto, moneda, valido)
                                     VALUES (:id_viaje_persona, :tipo_transporte, :ciudad_origen, :ciudad_destino, :fecha, :monto, :moneda, :valido)";
                $stmt_transporte = $this->db->prepare($query_transporte);

                foreach ($detalles_viajes as $index => $persona) {
                    // Insertar persona
                    try {
                        $result = $stmt_persona->execute([
                            'id_anticipo' => $id_anticipo,
                            'doc_identidad' => $persona['doc_identidad'],
                            'nombre_persona' => $persona['nombre_persona'],
                            'id_cargo' => $persona['id_cargo'],
                            'valido' => '1'
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
                                'moneda' => 'pen',
                                'valido' => '1'
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
            return $id_anticipo;
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
            $query = "INSERT INTO tb_historial_anticipos (id_anticipo, estado, id_usuario, fecha, comentario)
                      VALUES (:id_anticipo, :estado, :id_usuario, NOW(), :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'id_anticipo' => $id,
                'estado' => $estado,
                'id_usuario' => $id_usuario,
                'comentario' => $comentario ?? "Cambio a $estado"
            ]);
            return true;
        } catch (PDOException $e) {
            error_log('Error al registrar historial de anticipo: ' . $e->getMessage());
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
            // log para mostrar el saldo disponible
            //error_log('Consultando saldo para codigo_sscc ' . $codigo_sscc . ': ' . ($result ? $result['saldo_disponible'] : 'No encontrado'));
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
            $query = "SELECT 
                a.id,
                a.id_usuario,
                a.solicitante_nombres,
                a.dni_solicitante,
                a.departamento,
                a.departamento_nombre,
                a.codigo_sscc,
                a.cargo,
                a.nombre_proyecto,
                a.fecha_solicitud,
                a.motivo_anticipo,
                a.monto_total_solicitado,
                s.scc_codigo,
                (
                    SELECT h.estado
                    FROM tb_historial_anticipos h
                    WHERE h.id_anticipo = a.id
                    ORDER BY h.fecha DESC
                    LIMIT 1
                ) AS estado
                FROM tb_anticipos a
                LEFT JOIN tb_sscc s ON a.codigo_sscc = s.codigo
                WHERE a.id = :id_anticipo;";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id_anticipo' => $id_anticipo]);
            $anticipo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anticipo) {
                return null;
            }

            // Obtener detalles de compras menores
            $query_compras = "SELECT id, descripcion, motivo, moneda, importe 
                              FROM tb_detalles_compras_menores 
                              WHERE id_anticipo = :id_anticipo AND valido=1";
            $stmt_compras = $this->db->prepare($query_compras);
            $stmt_compras->execute(['id_anticipo' => $id_anticipo]);
            $anticipo['detalles_gastos'] = $stmt_compras->fetchAll(PDO::FETCH_ASSOC);



            //here iniicia
            // Obtener detalles de personas (viáticos y transporte)
            $query_personas = "SELECT vp.id, vp.doc_identidad, vp.nombre_persona, vp.id_cargo, vp.valido, c.nombre AS cargo_nombre
                               FROM tb_viajes_personas vp
                               LEFT JOIN tb_cargos_tarifario c ON vp.id_cargo = c.id
                               WHERE vp.id_anticipo = :id_anticipo and vp.valido = 1";
            $stmt_personas = $this->db->prepare($query_personas);
            $stmt_personas->execute(['id_anticipo' => $id_anticipo]);
            $personas = $stmt_personas->fetchAll(PDO::FETCH_ASSOC);

            $anticipo['detalles_viajes'] = [];
            foreach ($personas as $persona) {
                $id_viaje_persona = $persona['id'];
                $persona_data = [
                    'id' => $persona['id'],
                    'doc_identidad' => $persona['doc_identidad'],
                    'nombre_persona' => $persona['nombre_persona'],
                    'id_cargo' => $persona['id_cargo'],
                    'cargo_nombre' => $persona['cargo_nombre'],
                    'valido' => $persona['valido'],
                    'viaticos' => [],
                    'transporte' => []
                ];

                // Obtener viáticos
                        $query_viaticos = "SELECT dv.id, dv.id_concepto, c.nombre AS concepto_nombre, dv.dias, dv.monto, dv.moneda
                                        FROM tb_detalles_viajes dv
                                        LEFT JOIN tb_categorias_tarifario c ON dv.id_concepto = c.id
                                        WHERE dv.id_viaje_persona = :id_viaje_persona";
                $stmt_viaticos = $this->db->prepare($query_viaticos);
                $stmt_viaticos->execute(['id_viaje_persona' => $id_viaje_persona]);
                $persona_data['viaticos'] = $stmt_viaticos->fetchAll(PDO::FETCH_ASSOC);

                // Obtener transporte
                $query_transporte = "SELECT id, tipo_transporte, ciudad_origen, ciudad_destino, fecha, monto, moneda, valido
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

    // Actualizar un anticipo existente
    public function updateAnticipo($data) {
    try {
        $this->db->beginTransaction();

        // Validar estado del anticipo
        $query = "SELECT h.estado FROM tb_historial_anticipos h 
                  LEFT JOIN tb_anticipos a ON h.id_anticipo = a.id 
                  WHERE h.id_anticipo = :id_anticipo LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id_anticipo' => $data['id_anticipo']]);
        $anticipo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$anticipo || !in_array($anticipo['estado'], ['Nuevo'])) {
            throw new Exception("El anticipo no puede ser editado en su estado actual.");
        }

        // Recalcular monto total solicitado para validar
        $calculatedTotal = 0;
        if (!empty($data['detalles_gastos'])) {
            foreach ($data['detalles_gastos'] as $gasto) {
                if ($gasto['valido'] === '1') {
                    $calculatedTotal += floatval($gasto['importe']);
                }
            }
        }
        if (!empty($data['detalles_viajes'])) {
            foreach ($data['detalles_viajes'] as $viaje) {
                if ($viaje['valido'] === '1') {
                    if (!empty($viaje['transporte'])) {
                        foreach ($viaje['transporte'] as $transporte) {
                            if ($transporte['valido'] === '1') {
                                $calculatedTotal += floatval($transporte['monto']);
                            }
                        }
                    }
                    if (!empty($viaje['viaticos'])) {
                        foreach ($viaje['viaticos'] as $viatico) {
                            if ($viatico['dias'] > 0) {
                                $calculatedTotal += floatval($viatico['monto']);
                            }
                        }
                    }
                }
            }
        }

        // Validar monto total solicitado
        $montoTotalSolicitado = floatval($data['monto_total_solicitado']);
        if (abs($calculatedTotal - $montoTotalSolicitado) > 0.01) {
            throw new Exception("El monto total solicitado ($montoTotalSolicitado) no coincide con la suma de los detalles ($calculatedTotal).");
        }

        // Actualizar datos principales del anticipo
        $query = "UPDATE tb_anticipos SET 
                    codigo_sscc = :codigo_sscc,
                    nombre_proyecto = :nombre_proyecto,
                    motivo_anticipo = :motivo_anticipo,
                    monto_total_solicitado = :monto_total_solicitado
                  WHERE id = :id_anticipo";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'id_anticipo' => $data['id_anticipo'],
            'codigo_sscc' => $data['codigo_sscc'],
            'nombre_proyecto' => $data['nombre_proyecto'],
            'motivo_anticipo' => $data['motivo_anticipo'],
            'monto_total_solicitado' => $data['monto_total_solicitado']
        ]);

        // Obtener IDs existentes de detalles de compras menores
        $queryExistingGastos = "SELECT id FROM tb_detalles_compras_menores WHERE id_anticipo = :id_anticipo";
        $stmtExistingGastos = $this->db->prepare($queryExistingGastos);
        $stmtExistingGastos->execute(['id_anticipo' => $data['id_anticipo']]);
        $existingGastoIds = $stmtExistingGastos->fetchAll(PDO::FETCH_COLUMN);

        // Procesar detalles_gastos
        $processedGastoIds = [];
        if (!empty($data['detalles_gastos'])) {
            foreach ($data['detalles_gastos'] as $index => $gasto) {
                if ($gasto['valido'] === '1') {
                    if (isset($gasto['id']) && !empty($gasto['id'])) {
                        // Actualizar gasto existente
                        $query = "UPDATE tb_detalles_compras_menores 
                                  SET descripcion = :descripcion, 
                                      motivo = :motivo, 
                                      moneda = :moneda, 
                                      importe = :importe, 
                                      valido = :valido
                                  WHERE id = :id";
                        $stmt = $this->db->prepare($query);
                        $stmt->execute([
                            'id' => $gasto['id'],
                            'descripcion' => $gasto['descripcion'],
                            'motivo' => $gasto['motivo'],
                            'moneda' => $gasto['moneda'],
                            'importe' => $gasto['importe'],
                            'valido' => $gasto['valido']
                        ]);
                        $processedGastoIds[] = $gasto['id'];
                    } else {
                        // Insertar nuevo gasto
                        $query = "INSERT INTO tb_detalles_compras_menores 
                                  (id_anticipo, descripcion, motivo, moneda, importe, valido) 
                                  VALUES (:id_anticipo, :descripcion, :motivo, :moneda, :importe, :valido)";
                        $stmt = $this->db->prepare($query);
                        $stmt->execute([
                            'id_anticipo' => $data['id_anticipo'],
                            'descripcion' => $gasto['descripcion'],
                            'motivo' => $gasto['motivo'],
                            'moneda' => $gasto['moneda'],
                            'importe' => $gasto['importe'],
                            'valido' => $gasto['valido']
                        ]);
                        $processedGastoIds[] = $this->db->lastInsertId();
                    }
                } elseif (isset($gasto['id']) && !empty($gasto['id'])) {
                    // Marcar como no válido si existe en la base de datos
                    $query = "UPDATE tb_detalles_compras_menores 
                              SET valido = :valido
                              WHERE id = :id";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([
                        'id' => $gasto['id'],
                        'valido' => $gasto['valido']
                    ]);
                    $processedGastoIds[] = $gasto['id'];
                }
            }
        }

        // Marcar como no válidos los gastos no enviados
        foreach ($existingGastoIds as $existingId) {
            if (!in_array($existingId, $processedGastoIds)) {
                $query = "UPDATE tb_detalles_compras_menores 
                          SET valido = 0
                          WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(['id' => $existingId]);
            }
        }

        // Obtener IDs existentes de viajes
        $queryExistingViajes = "SELECT id FROM tb_viajes_personas WHERE id_anticipo = :id_anticipo";
        $stmtExistingViajes = $this->db->prepare($queryExistingViajes);
        $stmtExistingViajes->execute(['id_anticipo' => $data['id_anticipo']]);
        $existingViajeIds = $stmtExistingViajes->fetchAll(PDO::FETCH_COLUMN);

        // Procesar detalles_viajes
        $processedViajeIds = [];
        if (!empty($data['detalles_viajes'])) {
            foreach ($data['detalles_viajes'] as $index => $viaje) {
                $viajeId = $viaje['id'] ?? null;

                if (isset($viaje['valido']) && $viaje['valido'] === '0' && !empty($viajeId)) {
                    // Marcar todos los datos de la persona como inactivos
                    $query = "UPDATE tb_detalles_viajes 
                              SET dias = 0, monto = 0
                              WHERE id_viaje_persona = :id_viaje_persona";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(['id_viaje_persona' => $viajeId]);

                    $query = "UPDATE tb_transporte_provincial 
                              SET valido = 0
                              WHERE id_viaje_persona = :id_viaje_persona";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute(['id_viaje_persona' => $viajeId]);

                    $query = "UPDATE tb_viajes_personas 
                              SET valido = :valido
                              WHERE id = :id";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([
                        'id' => $viajeId,
                        'valido' => $viaje['valido']
                    ]);
                    $processedViajeIds[] = $viajeId;
                    continue; // Saltar el resto del procesamiento para esta persona
                }

                if ($viaje['valido'] === '1') {
                    if (isset($viaje['id']) && !empty($viaje['id'])) {
                        // Actualizar viaje existente
                        $query = "UPDATE tb_viajes_personas 
                                  SET doc_identidad = :doc_identidad, 
                                      nombre_persona = :nombre_persona, 
                                      id_cargo = :id_cargo, 
                                      valido = :valido
                                  WHERE id = :id";
                        $stmt = $this->db->prepare($query);
                        $stmt->execute([
                            'id' => $viaje['id'],
                            'doc_identidad' => $viaje['doc_identidad'],
                            'nombre_persona' => $viaje['nombre_persona'],
                            'id_cargo' => $viaje['id_cargo'],
                            'valido' => $viaje['valido']
                        ]);
                        $viajeId = $viaje['id'];
                        $processedViajeIds[] = $viajeId;
                    } else {
                        // Insertar nuevo viaje
                        $query = "INSERT INTO tb_viajes_personas 
                                  (id_anticipo, doc_identidad, nombre_persona, id_cargo, valido) 
                                  VALUES (:id_anticipo, :doc_identidad, :nombre_persona, :id_cargo, :valido)";
                        $stmt = $this->db->prepare($query);
                        $stmt->execute([
                            'id_anticipo' => $data['id_anticipo'],
                            'doc_identidad' => $viaje['doc_identidad'],
                            'nombre_persona' => $viaje['nombre_persona'],
                            'id_cargo' => $viaje['id_cargo'],
                            'valido' => $viaje['valido']
                        ]);
                        $viajeId = $this->db->lastInsertId();
                        $processedViajeIds[] = $viajeId;
                    }

                    // Procesar transporte
                    $queryExistingTransporte = "SELECT id FROM tb_transporte_provincial WHERE id_viaje_persona = :id_viaje_persona";
                    $stmtExistingTransporte = $this->db->prepare($queryExistingTransporte);
                    $stmtExistingTransporte->execute(['id_viaje_persona' => $viajeId]);
                    $existingTransporteIds = $stmtExistingTransporte->fetchAll(PDO::FETCH_COLUMN);

                    $processedTransporteIds = [];
                    if (!empty($viaje['transporte'])) {
                        foreach ($viaje['transporte'] as $tIndex => $transporte) {
                            if ($transporte['valido'] === '1') {
                                if (isset($transporte['id']) && !empty($transporte['id'])) {
                                    // Actualizar transporte existente
                                    $query = "UPDATE tb_transporte_provincial 
                                              SET tipo_transporte = :tipo_transporte, 
                                                  ciudad_origen = :ciudad_origen, 
                                                  ciudad_destino = :ciudad_destino, 
                                                  fecha = :fecha, 
                                                  monto = :monto, 
                                                  moneda = :moneda, 
                                                  valido = :valido
                                              WHERE id = :id";
                                    $stmt = $this->db->prepare($query);
                                    $stmt->execute([
                                        'id' => $transporte['id'],
                                        'tipo_transporte' => $transporte['tipo_transporte'],
                                        'ciudad_origen' => $transporte['ciudad_origen'],
                                        'ciudad_destino' => $transporte['ciudad_destino'],
                                        'fecha' => $transporte['fecha'],
                                        'monto' => $transporte['monto'],
                                        'moneda' => $transporte['moneda'],
                                        'valido' => $transporte['valido']
                                    ]);
                                    $processedTransporteIds[] = $transporte['id'];
                                } else {
                                    // Insertar nuevo transporte
                                    $query = "INSERT INTO tb_transporte_provincial 
                                              (id_viaje_persona, tipo_transporte, ciudad_origen, ciudad_destino, fecha, monto, moneda, valido) 
                                              VALUES (:id_viaje_persona, :tipo_transporte, :ciudad_origen, :ciudad_destino, :fecha, :monto, :moneda, :valido)";
                                    $stmt = $this->db->prepare($query);
                                    $stmt->execute([
                                        'id_viaje_persona' => $viajeId,
                                        'tipo_transporte' => $transporte['tipo_transporte'],
                                        'ciudad_origen' => $transporte['ciudad_origen'],
                                        'ciudad_destino' => $transporte['ciudad_destino'],
                                        'fecha' => $transporte['fecha'],
                                        'monto' => $transporte['monto'],
                                        'moneda' => $transporte['moneda'],
                                        'valido' => $transporte['valido']
                                    ]);
                                    $processedTransporteIds[] = $this->db->lastInsertId();
                                }
                            } elseif (isset($transporte['id']) && !empty($transporte['id'])) {
                                // Marcar como no válido si existe
                                $query = "UPDATE tb_transporte_provincial 
                                          SET valido = :valido
                                          WHERE id = :id";
                                $stmt = $this->db->prepare($query);
                                $stmt->execute([
                                    'id' => $transporte['id'],
                                    'valido' => $transporte['valido']
                                ]);
                                $processedTransporteIds[] = $transporte['id'];
                            }
                        }
                    }

                    // Marcar como no válidos los transportes no enviados
                    foreach ($existingTransporteIds as $existingId) {
                        if (!in_array($existingId, $processedTransporteIds)) {
                            $query = "UPDATE tb_transporte_provincial 
                                      SET valido = 0
                                      WHERE id = :id";
                            $stmt = $this->db->prepare($query);
                            $stmt->execute(['id' => $existingId]);
                        }
                    }

                    // Procesar viáticos
                    $queryExistingViaticos = "SELECT id FROM tb_detalles_viajes WHERE id_viaje_persona = :id_viaje_persona";
                    $stmtExistingViaticos = $this->db->prepare($queryExistingViaticos);
                    $stmtExistingViaticos->execute(['id_viaje_persona' => $viajeId]);
                    $existingViaticoIds = $stmtExistingViaticos->fetchAll(PDO::FETCH_COLUMN);

                    $processedViaticoIds = [];
                    
                    if (!empty($viaje['viaticos'])) {//here now
                        // Obtener cargo_id de la persona
                        $queryCargo = "SELECT id_cargo FROM tb_viajes_personas WHERE id = :id LIMIT 1";
                        $stmtCargo = $this->db->prepare($queryCargo);
                        $stmtCargo->execute(['id' => $viajeId]);
                        $cargoId = $stmtCargo->fetchColumn() ?: 1; // Default a 1 si no se encuentra

                        foreach ($viaje['viaticos'] as $concepto => $viatico) {
                            //error_log("Procesando viático: concepto=$concepto, id={$viatico['id'] ?? 'nuevo'}, dias={$viatico['dias']}, monto={$viatico['monto']}");
                            $conceptoId = $this->getConceptoIdByNombre($concepto);
                            if (!$conceptoId) {
                                throw new Exception("Concepto '$concepto' no encontrado en la base de datos.");
                            }

                            if (isset($viatico['id']) && !empty($viatico['id'])) {
                                if ($viatico['dias'] > 0) {
                                    // Recalcular monto basado en tb_tarifario
                                    $queryTarifa = "SELECT monto FROM tb_tarifario WHERE cargo_id = :cargo_id AND concepto_id = :concepto_id LIMIT 1";
                                    $stmtTarifa = $this->db->prepare($queryTarifa);
                                    $stmtTarifa->execute(['cargo_id' => $cargoId, 'concepto_id' => $conceptoId]);
                                    $tarifa = $stmtTarifa->fetchColumn() ?: 0;
                                    $monto = $viatico['dias'] * $tarifa;

                                    $query = "UPDATE tb_detalles_viajes 
                                            SET dias = :dias, 
                                                monto = :monto,
                                                moneda = :moneda
                                            WHERE id = :id";
                                    $stmt = $this->db->prepare($query);
                                    if (!$stmt->execute([
                                        'id' => $viatico['id'],
                                        'dias' => $viatico['dias'],
                                        'monto' => $monto,
                                        'moneda' => $viatico['moneda'] ?? 'PEN'
                                    ])) {
                                        throw new Exception("Error al actualizar tb_detalles_viajes para id {$viatico['id']}.");
                                    }
                                } else {
                                    $query = "UPDATE tb_detalles_viajes 
                                            SET dias = 0,
                                                monto = 0
                                            WHERE id = :id";
                                    $stmt = $this->db->prepare($query);
                                    if (!$stmt->execute(['id' => $viatico['id']])) {
                                        throw new Exception("Error al marcar tb_detalles_viajes como no válido para id {$viatico['id']}.");
                                    }
                                }
                                $processedViaticoIds[] = $viatico['id'];
                            } else {
                                // Recalcular monto para nueva inserción
                                $queryTarifa = "SELECT monto FROM tb_tarifario WHERE cargo_id = :cargo_id AND concepto_id = :concepto_id LIMIT 1";
                                $stmtTarifa = $this->db->prepare($queryTarifa);
                                $stmtTarifa->execute(['cargo_id' => $cargoId, 'concepto_id' => $conceptoId]);
                                $tarifa = $stmtTarifa->fetchColumn() ?: 0;
                                $monto = $viatico['dias'] * $tarifa;

                                $query = "INSERT INTO tb_detalles_viajes 
                                        (id_viaje_persona, id_concepto, dias, monto, moneda) 
                                        VALUES (:id_viaje_persona, :id_concepto, :dias, :monto, :moneda)";
                                $stmt = $this->db->prepare($query);
                                if (!$stmt->execute([
                                    'id_viaje_persona' => $viajeId,
                                    'id_concepto' => $conceptoId,
                                    'dias' => $viatico['dias'],
                                    'monto' => $monto,
                                    'moneda' => $viatico['moneda'] ?? 'PEN'
                                ])) {
                                    throw new Exception("Error al insertar en tb_detalles_viajes.");
                                }
                                $processedViaticoIds[] = $this->db->lastInsertId();
                            }
                        }
                    }


                } elseif (isset($viaje['id']) && !empty($viaje['id'])) {
                    // Marcar como no válido si existe
                    $query = "UPDATE tb_viajes_personas 
                              SET valido = :valido
                              WHERE id = :id";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([
                        'id' => $viaje['id'],
                        'valido' => $viaje['valido']
                    ]);
                    $processedViajeIds[] = $viaje['id'];
                }
            }
        }

        // Marcar como no válidos los viajes no enviados
        foreach ($existingViajeIds as $existingId) {
            if (!in_array($existingId, $processedViajeIds)) {
                $query = "UPDATE tb_viajes_personas 
                          SET valido = 0
                          WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(['id' => $existingId]);
            }
        }

        $this->db->commit();
        error_log("Se registró la actualización del anticipo");
        return ['success' => true];
    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("Error en updateAnticipo: " . $e->getMessage());
        return ['error' => $e->getMessage()];
    }
}


    private function getConceptoIdByNombre($nombre) {
        $normalizedNombre = strtolower(trim($nombre));
        $query = "SELECT id FROM tb_categorias_tarifario WHERE LOWER(nombre) = :nombre AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['nombre' => $normalizedNombre]);
        $conceptoId = $stmt->fetchColumn();
        if ($conceptoId === false) {
            error_log("Concepto '$normalizedNombre' no encontrado en tb_categorias_tarifario.");
        }
        return $conceptoId ?: null;
    }

    // Métodos para el dashboard
    public function getCountAllAnticiposById($id){
        $query = "SELECT COUNT(*) AS cantidad_solicitudes FROM tb_anticipos WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id_usuario' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Métodos para el dashboard
    public function getCountAnticiposByState($id, $estado) {
        $query = "
            SELECT COUNT(*) AS cantidad
            FROM tb_anticipos a
            WHERE a.id_usuario = :id_usuario
            AND a.id IN (
                SELECT id_anticipo
                FROM tb_historial_anticipos h
                WHERE h.id = (
                    SELECT MAX(id)
                    FROM tb_historial_anticipos
                    WHERE id_anticipo = h.id_anticipo
                )
                AND h.estado = :estado
            )";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id_usuario' => $id, 'estado' => $estado]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function abonarAnticipo($id_anticipo, $id_usuario, $comentario) {
        try {
            $this->db->beginTransaction();
            error_log("Transacción iniciada para abonar anticipo ID: $id_anticipo");

            // Obtener datos del anticipo
            $query = "SELECT id_usuario, monto_total_solicitado, codigo_sscc FROM tb_anticipos WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id_anticipo]);
            $anticipo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anticipo) {
                throw new Exception('Anticipo no encontrado');
            }

            $monto = $anticipo['monto_total_solicitado'];
            $codigo_sscc = $anticipo['codigo_sscc'];
            $anticipo_usuario = $anticipo['id_usuario'];

            // Obtener el ID del presupuesto asociado
            $query = "SELECT id, saldo_disponible FROM tb_presupuestos_sscc WHERE codigo_sscc = :codigo_sscc AND activo = 1 FOR UPDATE";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':codigo_sscc' => $codigo_sscc]);
            $presupuesto = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$presupuesto) {
                throw new Exception('Presupuesto asociado no encontrado');
            }

            $id_presupuesto = $presupuesto['id'];
            $nuevo_saldo_disponible = $presupuesto['saldo_disponible'] - $monto;

            // Validar que el saldo no sea negativo
            if ($nuevo_saldo_disponible < 0) {
                throw new Exception('No hay saldo suficiente en el presupuesto.');
            }

            // Actualizar el presupuesto
            $query = "UPDATE tb_presupuestos_sscc 
                      SET saldo_disponible = :saldo_disponible, ultima_actualizacion = NOW() 
                      WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':saldo_disponible' => $nuevo_saldo_disponible,
                ':id' => $id_presupuesto
            ]);

            // Registrar movimiento en tb_movimientos_presupuesto
            $query = "INSERT INTO tb_movimientos_presupuesto (id_presupuesto, tipo_movimiento, monto, id_anticipo, id_usuario, fecha, comentario)
                      VALUES (:id_presupuesto, :tipo_movimiento, :monto, :id_anticipo, :id_usuario, NOW(), :comentario)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':id_presupuesto' => $id_presupuesto,
                ':tipo_movimiento' => 2,
                ':monto' => $monto,
                ':id_anticipo' => $id_anticipo,
                ':id_usuario' => $id_usuario,
                ':comentario' => 'Anticipo'
            ]);


            // Crear rendición asociada al anticipo
            $fecha_inicio = date('Y-m-d H:i:s');
            $fecha_rendicion = date('Y-m-d H:i:s', strtotime('+3 days'));
            $id_cat_documento = 2;
            $id_rendicion = $this->rendicionesModel->createRendicion($id_anticipo, $anticipo_usuario, $fecha_inicio, $fecha_rendicion, $id_cat_documento, $monto, $comentario);

            if ($id_rendicion) {
                // Registrar estado 'Nuevo' en historial de rendiciones
                if ($this->rendicionesModel->updateEstado($id_rendicion, 'Nuevo', $id_usuario, 'Rendición registrada tras registro de abono de anticipo')) {
                    // Actualizar estado del anticipo y registrar en historial
                    if ($this->updateAnticipoEstado($id_anticipo, 'Abonado', $id_usuario, $comentario)) {
                        $this->db->commit();
                        error_log("Transacción completada para anticipo ID: $id_anticipo");
                        return true;
                    }
                }
                throw new Exception('Error al registrar el historial de la rendición.');
            }

            throw new Exception('Error al crear la rendición asociada.');

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error al abonar anticipo: ' . $e->getMessage());
            return false;
        }
    }
    
    public function getLatestAnticipoEstado($id_anticipo) {
        try {
            $query = "SELECT estado
                    FROM tb_historial_anticipos
                    WHERE id_anticipo = :id_anticipo
                    AND (id_anticipo, fecha) IN (
                        SELECT id_anticipo, MAX(fecha)
                        FROM tb_historial_anticipos
                        GROUP BY id_anticipo
                    )";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id_anticipo' => $id_anticipo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['estado'] : null;
        } catch (PDOException $e) {
            error_log('Error al obtener el estado más reciente del anticipo: ' . $e->getMessage());
            return null;
        }
    }

    public function getDetallesViaticosByAnticipo($id_anticipo) {
        // Datos generales del anticipo
        $query_anticipo = "SELECT id AS anticipo_id, monto_total_solicitado, solicitante_nombres, dni_solicitante 
                        FROM tb_anticipos 
                        WHERE id = :id_anticipo";
        $stmt_anticipo = $this->db->prepare($query_anticipo);
        $stmt_anticipo->execute([':id_anticipo' => $id_anticipo]);
        $anticipo = $stmt_anticipo->fetch(PDO::FETCH_ASSOC);

        // Personas asociadas al anticipo
        $query_personas = "SELECT id AS viaje_id, doc_identidad, nombre_persona 
                        FROM tb_viajes_personas 
                        WHERE id_anticipo = :id_anticipo";
        $stmt_personas = $this->db->prepare($query_personas);
        $stmt_personas->execute([':id_anticipo' => $id_anticipo]);
        $personas = $stmt_personas->fetchAll(PDO::FETCH_ASSOC);

        // Obtener n_cuenta para el solicitante y las personas asociadas
        $dnis = array_merge([$anticipo['dni_solicitante']], array_column($personas, 'doc_identidad'));
        $dnis = array_filter($dnis); // Eliminar valores nulos o vacíos
        $n_cuentas = [];
        if (!empty($dnis)) {
            $placeholders = implode(',', array_fill(0, count($dnis), '?'));
            $query_n_cuentas = "SELECT dni, n_cuenta FROM tb_usuarios WHERE dni IN ($placeholders)";
            $stmt_n_cuentas = $this->db->prepare($query_n_cuentas);
            $stmt_n_cuentas->execute($dnis);
            $n_cuentas = $stmt_n_cuentas->fetchAll(PDO::FETCH_KEY_PAIR); // dni => n_cuenta
        }

        // Añadir n_cuenta al solicitante
        if ($anticipo && isset($anticipo['dni_solicitante'])) {
            $anticipo['n_cuenta_solicitante'] = $n_cuentas[$anticipo['dni_solicitante']] ?? null;
        }

        // Añadir n_cuenta a las personas asociadas
        foreach ($personas as &$persona) {
            $persona['n_cuenta'] = $n_cuentas[$persona['doc_identidad']] ?? null;
        }
        unset($persona);

        // Detalles de transporte
        $query_transporte = "SELECT id AS transporte_id, id_viaje_persona, tipo_transporte, ciudad_origen, ciudad_destino, fecha, monto AS monto_transporte, moneda AS moneda_transporte, valido AS valido_transporte 
                            FROM tb_transporte_provincial 
                            WHERE id_viaje_persona IN (SELECT id FROM tb_viajes_personas WHERE id_anticipo = :id_anticipo) AND valido = 1";
        $stmt_transporte = $this->db->prepare($query_transporte);
        $stmt_transporte->execute([':id_anticipo' => $id_anticipo]);
        $transporte = $stmt_transporte->fetchAll(PDO::FETCH_ASSOC);

        // Detalles de viáticos
        $query_detalles = "SELECT id AS detalle_id, id_viaje_persona, id_concepto, dias, monto AS monto_detalle, moneda AS moneda_detalle 
                        FROM tb_detalles_viajes 
                        WHERE id_viaje_persona IN (SELECT id FROM tb_viajes_personas WHERE id_anticipo = :id_anticipo)";
        $stmt_detalles = $this->db->prepare($query_detalles);
        $stmt_detalles->execute([':id_anticipo' => $id_anticipo]);
        $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

        // Retornar un arreglo con todos los datos
        return [
            'anticipo' => $anticipo,
            'personas' => $personas,
            'transporte' => $transporte,
            'detalles' => $detalles
        ];
    }

    // Nuevo método para obtener el id_usuario del último autorizador
    public function getLastAuthorizerId($idAnticipo) {
        try {
            $query = "SELECT id_usuario FROM tb_historial_anticipos WHERE id_anticipo = :id AND estado = 'Autorizado' ORDER BY fecha DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $idAnticipo]);
            $idAutorizador = $stmt->fetchColumn();
            //error_log("ID del último autorizador para anticipo $idAnticipo: " . ($idAutorizador ?: 'No encontrado'));
            return $idAutorizador ?: null;
        } catch (PDOException $e) {
            error_log('Error al obtener el último autorizador: ' . $e->getMessage());
            return null;
        }
    }
}
?>