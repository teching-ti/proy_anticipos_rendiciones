<?php
require_once 'src/config/Database.php';
require_once 'src/models/TrabajadorModel.php';
require_once 'src/models/AnticipoModel.php';
require_once 'src/config/EmailConfig.php';

class AnticipoController {
    private $db;
    private $anticipoModel;
    private $emailConfig;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->anticipoModel = new AnticipoModel();
        $this->trabajadorModel = new TrabajadorModel();
        $this->emailConfig = new EmailConfig();
    }

    public function index() {
        if (!isset($_SESSION['id'])) {
            header('Location: iniciar_sesion');
            exit;
        }
        $anticipos_data = $this->anticipoModel->getAnticiposByRole($_SESSION['id'], $_SESSION['rol']);
        // $jefes = $this->anticipoModel->getJefes();
        $sccs = $this->anticipoModel->getAllScc();
        //$ssccs = $this->anticipoModel->getAllSscc();
        $ssccs = [];
        require_once 'src/views/anticipos.php';
    }

    // Funcionalidad para obtener SSCC por SCC filtrado del select
    public function getSsccByScc(){
        header('Content-Type: application/json');

        if (!isset($_GET['codigo_scc'])) {
            http_response_code(400);
            echo json_encode(['error' => 'codigo scc no proporcionado']);
            return;
        }

        $codigo_scc = $_GET['codigo_scc'];
        $ssccs = $this->anticipoModel->getSsccByScc($codigo_scc);
        echo json_encode($ssccs);
        exit;
    }

    public function getAllScc() {
        header('Content-Type: application/json');
        try {
            $sccs = $this->anticipoModel->getAllScc();
            echo json_encode($sccs);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // función que se utiliza en tiempo real para consultar el saldo disponible cada vez que se calcula el monto total cuando se va llenando el formulario de anticipos
    public function getSaldoDisponibleTiempoReal(){
        header('Content-Type: application/json');

        if (!isset($_GET['codigo_sscc'])) {
            http_response_code(400);
            echo json_encode(['error' => 'codigo sscc no proporcionado']);
            return;
        }

        $codigo_sscc = $_GET['codigo_sscc'];
        $ssccs = $this->anticipoModel->getSaldoDisponibleBySscc($codigo_sscc);
        echo json_encode($ssccs);
        exit;

    }

    // Funcionalidad para agregar un anticipo
    public function add() {

        if (!isset($_SESSION['id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sesión no iniciada.']);
            exit;
        }
        
        $sccs = $this->anticipoModel->getAllScc();
        $ssccs2 = $this->anticipoModel->getAllSscc();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $response = ['success' => false, 'message' => '']; 
            
            $id_usuario = $_SESSION['id'];
            $solicitante = $_SESSION['nombre_usuario'];
            $solicitante_nombres = trim($_POST['solicitante'] ?? '');
            $dni_solicitante = $_SESSION['dni'];
            $departamento = $_SESSION['trabajador']['departamento'];
            $departamento_nombre = $_SESSION['trabajador']['departamento_nombre'];
            $cargo = $_SESSION['trabajador']['cargo'];
            $codigo_sscc = trim($_POST['codigo_sscc'] ?? '');
            $nombre_proyecto = trim($_POST['nombre_proyecto'] ?? '');
            $fecha_solicitud = trim($_POST['fecha_solicitud'] ?? '');
            $motivo_anticipo = trim($_POST['motivo-anticipo'] ?? '');
            $monto_total_solicitado = (float)($_POST['monto-total'] ?? 0);
            $id_cat_documento = trim($_POST['id_cat_documento'] ?? '1');

            $concepto = trim($_POST['concepto'] ?? 'compras-menores');
            // arreglo en donde se encuentran los detalles de compras menores o gastos
            $detalles_gastos = isset($_POST['detalles_gastos']) ? $_POST['detalles_gastos'] : [];
            $detalles_viajes = [];

            // Procesar datos de viajes
            if ($concepto === 'viajes') {
                // Obtener conceptos válidos de tb_viaticos_concepto
                $conceptos_validos = array_column($this->anticipoModel->getConceptosViaticos(), 'id', 'nombre');
                foreach ($_POST as $key => $value) {
                    if (preg_match('/^doc-id-(\d+)$/', $key, $matches)) {
                        $index = $matches[1];
                        $detalles_viajes[$index] = [
                            'doc_identidad' => $value,
                            'nombre_persona' => $_POST["persona-nombre-$index"] ?? '',
                            'id_cargo' => (int)($_POST["cargo-nombre-$index"] ?? 0),
                            'viaticos' => [],
                            'transporte' => []
                        ];
                        foreach (['alimentacion', 'hospedaje', 'movilidad'] as $tipo) {
                            $id_concepto = $conceptos_validos[$tipo] ?? null;
                            if ($id_concepto && isset($_POST["dias-$tipo-$index"]) && (int)$_POST["dias-$tipo-$index"] > 0) {
                                $detalles_viajes[$index]['viaticos'][] = [
                                    'id_concepto' => $id_concepto,
                                    'dias' => (int)($_POST["dias-$tipo-$index"] ?? 0),
                                    'monto' => (float)($_POST["monto-$tipo-$index"] ?? 0)
                                ];
                            }
                        }
                    }
                    if (preg_match('/^gasto-viaje-(\d+)-(\d+)$/', $key, $matches)) {
                        $index = $matches[1];
                        $subindex = $matches[2];
                        $detalles_viajes[$index]['transporte'][] = [
                            'tipo_transporte' => $_POST["tipo-transporte-$index-$subindex"] ?? '',
                            'ciudad_origen' => $_POST["ciudad-origen-$index-$subindex"] ?? '',
                            'ciudad_destino' => $_POST["ciudad-destino-$index-$subindex"] ?? '',
                            'fecha' => $_POST["fecha-$index-$subindex"] ?? '',
                            'monto' => (float)($value ?? 0)
                        ];
                    }
                }
            }

            // Normalizar moneda y convertir importes a float para compras menores
            foreach ($detalles_gastos as &$detalle) {
                $detalle['moneda'] = strtoupper($detalle['moneda']);
                $detalle['importe'] = (float)$detalle['importe'];
            }
            unset($detalle);

            error_log("Datos recibidos en controlador: codigo_sscc=$codigo_sscc, proyecto=$nombre_proyecto, fecha=$fecha_solicitud, motivo=$motivo_anticipo, monto=$monto_total_solicitado, id_cat_documento=$id_cat_documento, concepto=$concepto, detalles_gastos=" . json_encode($detalles_gastos) . ", detalles_viajes=" . json_encode($detalles_viajes));

            // Validaciones
            if (empty($codigo_sscc) || empty($nombre_proyecto) || empty($fecha_solicitud) || empty($motivo_anticipo) || $monto_total_solicitado <= 0 || !$id_cat_documento) {
                $response['message'] = 'Los campos sub-subcentro, proyecto, fecha, motivo y monto son obligatorios. El monto debe ser mayor a 0.';
                error_log($response['message']);
            } elseif (!preg_match('/^.+$/', $nombre_proyecto)) {
                $response['message'] = 'El nombre del proyecto solo puede contener letras, números y espacios.';
                error_log($response['message']);
            } elseif (!preg_match('/^.+$/', $motivo_anticipo)) {
                $response['message'] = 'El motivo del anticipo solo puede contener letras, números y espacios.';
                error_log($response['message']);
            } elseif (!preg_match('/^.+$/', $fecha_solicitud) || !strtotime($fecha_solicitud)) {
                $response['message'] = 'La fecha de solicitud debe tener el formato YYYY-MM-DD.';
                error_log($response['message']);
            } else {
                if ($concepto == 'compras-menores' && !empty($detalles_gastos)) {
                    // Validar detalles de gastos menores
                    foreach ($detalles_gastos as $index => $detalle) {
                        if (empty($detalle['descripcion']) || empty($detalle['motivo']) || !isset($detalle['importe']) || empty($detalle['moneda'])) {
                            $response['message'] = "El detalle de gasto #$index tiene campos incompletos.";
                            error_log($response['message']);
                        }
                        if (!preg_match('/^.+$/', $detalle['motivo'])) {
                            $response['message'] = "El motivo del detalle de gasto #$index contiene caracteres no permitidos.";
                            error_log($response['message']);
                        }
                    }
                }

                // Validar detalles de viajes
                if ($concepto === 'viajes' && !empty($detalles_viajes)) {
                    foreach ($detalles_viajes as $index => $persona) {
                        error_log("Validando persona de viaje #$index: " . json_encode($persona));
                        if (empty($persona['doc_identidad']) || !preg_match('/^[0-9A-Za-z]{1,11}$/', $persona['doc_identidad'])) {
                            $response['message'] = "El documento de identidad de la persona #$index es inválido.";
                            error_log($response['message']);
                        }
                        if (empty($persona['nombre_persona']) || !preg_match('/^.+$/', $persona['nombre_persona'])) {
                            $response['message'] = "El nombre de la persona #$index es inválido.";
                            error_log($response['message']);
                        }
                        if (empty($persona['id_cargo']) || !$this->anticipoModel->cargoExists($persona['id_cargo'])) {
                            $response['message'] = "El cargo de la persona #$index es inválido.";
                            error_log($response['message']);
                        }
                        foreach ($persona['viaticos'] as $viatico) {
                            if ($viatico['dias'] < 0 || $viatico['monto'] < 0) {
                                $response['message'] = "Los días y el monto de viáticos para la persona #$index deben ser no negativos.";
                                error_log($response['message']);
                            }
                            // Validar monto contra tb_tarifario
                            $tarifa = $this->anticipoModel->getTarifaByCargoAndConcepto($persona['id_cargo'], $viatico['id_concepto']);
                            if ($tarifa && abs($viatico['monto'] - ($viatico['dias'] * $tarifa)) > 0.01) {
                                $response['message'] = "El monto de viático (concepto ID {$viatico['id_concepto']}) para la persona #$index no coincide con la tarifa oficial.";
                                error_log($response['message']);
                            }
                        }
                        foreach ($persona['transporte'] as $subindex => $transp) {
                            if (empty($transp['tipo_transporte']) || !in_array($transp['tipo_transporte'], ['terrestre', 'aereo'])) {
                                $response['message'] = "El tipo de transporte #$subindex para la persona #$index es inválido.";
                                error_log($response['message']);
                            }
                            if (empty($transp['ciudad_origen']) || empty($transp['ciudad_destino']) || !preg_match('/^[a-zA-Z\sáéíóúÁÉÍÓÚñÑ]+$/', $transp['ciudad_origen']) || !preg_match('/^[a-zA-Z\sáéíóúÁÉÍÓÚñÑ]+$/', $transp['ciudad_destino'])) {
                                $response['message'] = "El tipo de transporte #$subindex para la persona #$index es inválido.";
                                error_log($response['message']);
                            }
                            if (empty($transp['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $transp['fecha'])) {
                                $response['message'] = "Las ciudades de transporte #$subindex para la persona #$index son inválidas.";
                                error_log($response['message']);
                            }
                            if ($transp['monto'] <= 0) {
                                $response['message'] = "La fecha de transporte #$subindex para la persona #$index es inválida.";
                                error_log($response['message']);
                            }
                        }
                    }
                }

                if ($response['message'] === '') {
                    //url usada en el correo
                    $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/anticipos';

                    if ($this->anticipoModel->anticipoPendiente($id_usuario)) {
                    $response['message'] = 'El solicitante aún tiene pendiente un anticipo por rendir.';
                    error_log($response['message']);
                    } else {
                        // Verificar saldo disponible
                        $saldo_disponible = $this->anticipoModel->getSaldoDisponibleBySscc($codigo_sscc);
                        if ($saldo_disponible === null) {
                            $response['message'] = 'No se encontró un presupuesto activo para el sub-subcentro seleccionado.';
                            error_log($response['message']);
                        } elseif ($monto_total_solicitado > $saldo_disponible) {
                            $response['message'] = "No se registró el anticipo. El monto solicitado ($monto_total_solicitado) excede el saldo disponible ($saldo_disponible) para el sub-subcentro.";
                            error_log($response['message']);
                        } else {

                            $numero_anticipo = $this->anticipoModel->addAnticipo($id_usuario, $solicitante, $solicitante_nombres, $dni_solicitante, $departamento, $departamento_nombre, $codigo_sscc, $cargo, $nombre_proyecto, $fecha_solicitud, $motivo_anticipo, $monto_total_solicitado, $id_cat_documento, $detalles_gastos, $detalles_viajes);

                            if ($numero_anticipo) {
                                $response['success'] = true;
                                $response['message'] = 'Anticipo registrado con éxito';

                                $solicitante = $this->trabajadorModel->getTrabajadorByDni($dni_solicitante);

                                if ($solicitante && isset($solicitante['correo'])) {
                                    // formatear
                                    $fecha_correo = date("d-m-Y", strtotime($fecha_solicitud));
                                    $monto_correo = number_format($monto_total_solicitado, 2, ',', '.');

                                    $to = $solicitante['correo'];
                                    $subject = "SIAR - TECHING - Anticipo Número $numero_anticipo ha sido creado";

                                    $aprobadores = $this->trabajadorModel->getAprobadoresByDepartamento($departamento);

                                    $body = "
                                        <h2>Notificación de Anticipo</h2>
                                        <p>Estimado/a, {$solicitante['nombres']} {$solicitante['apellidos']},</p>
                                        <p>Se ha creado un nuevo anticipo con los siguientes detalles:</p>
                                        <ul>
                                            <li><strong>Número de Anticipo:</strong> $numero_anticipo</li>
                                            <li><strong>Motivo:</strong> $motivo_anticipo</li>
                                            <li><strong>DNI Solicitante:</strong> $dni_solicitante</li>
                                            <li><strong>Sub sub-centro de costo:</strong> $codigo_sscc</li>
                                            <li><strong>Nombre del Proyecto:</strong> $nombre_proyecto</li>
                                            <li><strong>Fecha:</strong> $fecha_correo</li>
                                            <li><strong>Monto:</strong> $monto_correo</li>
                                        </ul>
                                        <p>Recuerde que este anticipo deberá ser autorizado para que se pueda continuar con el flujo del anticipo.</p>
                                        <hr>
                                        <br>
                                        <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles del anticipo.</p>
                                        <a href='{$url_plataforma}'>SIAR - TECHING</a>
                                    ";

                                    if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $aprobadores)) {
                                        error_log("Anticipo creado y notificación enviada");
                                    } else {
                                        error_log("No se pudo enviar la notificación");
                                    }
                                } else {
                                    error_log("No se envió el correo, no se encontró el correo del solicitante");
                                }

                                error_log($response['message']);
                            } else {
                                $response['message'] = 'Error al registrar el anticipo.';
                                error_log($response['message']);
                            }
                        }
                    }
                }
            }
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        require_once 'src/views/anticipos.php';
    }

    public function update() {// here, este es el evento que de edición. Se deberá notificar de igual manera y colocar como nuevo tras editar uno observado.
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_anticipo' => $_POST['edit-id-anticipo'] ?? null,
                'codigo_sscc' => $_POST['edit-codigo-sscc'] ?? null,
                'nombre_proyecto' => $_POST['edit-nombre-proyecto'] ?? null,
                'motivo_anticipo' => $_POST['edit-motivo-anticipo'] ?? null,
                'monto_total_solicitado' => $_POST['edit-monto-total'] ?? 0,
                'detalles_gastos' => $_POST['edit-detalles_gastos'] ?? [],
                'detalles_viajes' => $_POST['edit-detalles_viajes'] ?? []
            ];

            // Procesar detalles_gastos
            foreach ($_POST as $key => $value) {
                if (preg_match('/^edit-detalles_gastos\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $field = $matches[2];
                    // Asegurar que el campo 'valido' esté presente
                    if (!isset($data['detalles_gastos'][$index]['valido'])) {
                        $data['detalles_gastos'][$index]['valido'] = '1';
                    }
                }
            }

            // Procesar detalles_viajes
            foreach ($_POST as $key => $value) {
                if (preg_match('/^edit-detalles_viajes\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $field = $matches[2];
                    $data['detalles_viajes'][$index][$field] = $value;
                    // Asegurar que el campo 'valido' esté presente
                    if (!isset($data['detalles_viajes'][$index]['valido'])) {
                        $data['detalles_viajes'][$index]['valido'] = '1';
                    }
                } elseif (preg_match('/^edit-detalles_viajes\[(\d+)\]\[transporte\]\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $transporteIndex = $matches[2];
                    $field = $matches[3];
                    $data['detalles_viajes'][$index]['transporte'][$transporteIndex][$field] = $value;
                    // Asegurar que el campo 'valido' esté presente
                    if (!isset($data['detalles_viajes'][$index]['transporte'][$transporteIndex]['valido'])) {
                        $data['detalles_viajes'][$index]['transporte'][$transporteIndex]['valido'] = '1';
                    }
                } elseif (preg_match('/^edit-dias-(hospedaje|movilidad|alimentacion)-(\d+)$/', $key, $matches)) {
                    $concepto = $matches[1];
                    $index = $matches[2] - 1;
                    $data['detalles_viajes'][$index]['viaticos'][strtolower($concepto)]['dias'] = $value;
                } elseif (preg_match('/^edit-monto-(hospedaje|movilidad|alimentacion)-(\d+)$/', $key, $matches)) {
                    $concepto = $matches[1];
                    $index = $matches[2] - 1;
                    $data['detalles_viajes'][$index]['viaticos'][strtolower($concepto)]['monto'] = $value;
                } elseif (preg_match('/^edit-tipo-transporte-(\d+)-(\d+)$/', $key, $matches)) {
                    $index = $matches[1] - 1;
                    $transporteIndex = $matches[2];
                    $data['detalles_viajes'][$index]['transporte'][$transporteIndex]['tipo_transporte'] = $value;
                }
            }

            error_log("Datos recibidos en update: " . print_r($data, true));

            if (!$data['id_anticipo'] || !$data['codigo_sscc'] || !$data['nombre_proyecto'] || !$data['motivo_anticipo']) {
                echo json_encode(['error' => 'Faltan datos requeridos']);
                return;
            }

            if (!is_numeric($data['monto_total_solicitado']) || $data['monto_total_solicitado'] <= 0) {
                echo json_encode(['error' => 'Monto total inválido']);
                return;
            }

            $saldo_disponible = $this->anticipoModel->getSaldoDisponibleBySscc($data['codigo_sscc']);
            if ($data['monto_total_solicitado'] > $saldo_disponible) {
                echo json_encode(['error' => 'El monto total solicitado excede el saldo disponible']);
                return;
            }

            foreach ($data['detalles_gastos'] as $index => $gasto) {
                if ($gasto['valido'] === '1') {
                    if (empty($gasto['descripcion']) || empty($gasto['motivo']) || empty($gasto['moneda']) || !isset($gasto['importe']) || $gasto['importe'] < 0) {
                        echo json_encode(['error' => "Datos incompletos o inválidos en detalles_gastos[$index]"]);
                        return;
                    }
                    if (!in_array($gasto['moneda'], ['PEN', 'USD'])) {
                        echo json_encode(['error' => "Moneda inválida en detalles_gastos[$index]"]);
                        return;
                    }
                    if ($gasto['descripcion'] !== 'Combustible' && $gasto['importe'] > 400) {
                        echo json_encode(['error' => "El importe no puede exceder 400 para el tipo de gasto en detalles_gastos[$index]"]);
                        return;
                    }
                }
            }

            foreach ($data['detalles_viajes'] as $index => $viaje) {
                if ($viaje['valido'] === '1') {
                    if (empty($viaje['doc_identidad']) || empty($viaje['nombre_persona']) || empty($viaje['id_cargo'])) {
                        echo json_encode(['error' => "Datos incompletos en detalles_viajes[$index]"]);
                        return;
                    }
                    foreach ($viaje['transporte'] as $tIndex => $transporte) {
                        if ($transporte['valido'] === '1') {
                            if (empty($transporte['tipo_transporte']) || empty($transporte['ciudad_origen']) || empty($transporte['ciudad_destino']) || empty($transporte['fecha']) || empty($transporte['monto']) || empty($transporte['moneda'])) {
                                echo json_encode(['error' => "Datos incompletos en detalles_viajes[$index][transporte][$tIndex]"]);
                                return;
                            }
                        }
                    }
                    foreach ($viaje['viaticos'] as $concepto => $viatico) {
                        if (!isset($viatico['dias']) || !isset($viatico['monto']) || $viatico['dias'] < 0 || $viatico['monto'] < 0) {
                            echo json_encode(['error' => "Datos incompletos o inválidos en detalles_viajes[$index][viaticos][$concepto]"]);
                            return;
                        }
                    }
                }
            }

            $result = $this->anticipoModel->updateAnticipo($data);
            error_log(print_r($result, true));

            if (!empty($result['success'])) {
                $id_anticipo = $data['id_anticipo'];

                // obtener el estado actual del anticipo
                $latestEstado = $this->anticipoModel->getLatestAnticipoEstado($id_anticipo);

                if (in_array($latestEstado, ['Observado'])) {
                    $motivo = $data['motivo_anticipo'];
                    $sscc = $data['codigo_sscc'];
                    $nombreProyecto = $data['nombre_proyecto'];
                    $montoTotal = $data['monto_total_solicitado'];
                    $dniSolicitante = $_SESSION['dni'];
                    $solicitanteNombre = $_SESSION['trabajador']['nombres'] . " " . $_SESSION['trabajador']['apellidos'];


                    // Esta notificación deberá darse siempre y cuando el estado actual o anterior sea "obsevado"
                    if ($this->anticipoModel->updateAnticipoEstado($id_anticipo, 'Nuevo', $_SESSION['id'], 'Anticipo actualizado tras observacioón')) {

                        $aprobadores = $this->trabajadorModel->getAprobadoresByDepartamento($_SESSION['trabajador']['departamento']);
                        //url usada en el correo
                        $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/anticipos';

                        $solicitante = $this->trabajadorModel->getTrabajadorByDni($_SESSION['dni']);
                        $correo_solicitante = $solicitante['correo'];


                        if ($correo_solicitante) {

                            $to = $correo_solicitante;
                            $subject = "SIAR - TECHING - Anticipo N° $id_anticipo ha sido actualizado";

                            $body = "
                                <h2>Notificación de Anticipo</h2>
                                <p>El anticipo ha sido actualizado tras haber sido marcado como observado. Información del anticipo:</p>
                                <ul>
                                    <li><strong>N° de Anticipo:</strong> $id_anticipo</li>
                                    <li><strong>Motivo:</strong> $motivo</li>
                                    <li><strong>DNI Solicitante:</strong> $dniSolicitante</li>
                                    <li><strong>Nombre Solicitante: $solicitanteNombre</strong></li>
                                    <li><strong>Sub sub-centro de costo:</strong> $sscc</li>
                                    <li><strong>Nombre del Proyecto:</strong> $nombreProyecto</li>
                                    <li><strong>Monto:</strong> $montoTotal</li>
                                </ul>
                                <p>Recuerde que el anticipo deberá ser autorizado por el área de <b>contabilidad</b> para continuar con la atención de su solicitud.</p>
                                <hr>
                                <br>
                                <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles del anticipo.</p>
                                <a href='{$url_plataforma}'>SIAR - TECHING</a>
                            ";

                            error_log("Correo del solciitante" .$correo_solicitante);

                            if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $aprobadores)) {
                                error_log("Anticipo actualizado y notificación enviada");
                            } else {
                                error_log("No se pudo enviar la notificación");
                            }


                        } else {
                            error_log("No se envió el correo, no se encontró el correo del solicitante");
                        }
                    }
                }

                echo json_encode(['success' => true]);
                // Aquí se debería de agregar un nuevo estado al historial del anticipo
            } else {
                echo json_encode(['error' => $result['error'] ?? 'Error desconocido']);
            }
        } else {
            echo json_encode(['error' => 'Método no permitido']);
        }
    }

    // Funcionalidad para autorizar un anticipo
    public function autorizar() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
            $_SESSION['error'] = 'No tienes permiso para autorizar anticipos.';
            error_log( 'No tiene permisos para realizar este tipo de aprobación');
            header("Location: /".$_SESSION['ruta_base']."/anticipos");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            
            // Elementos enviados para la notificación por correo
            $dniSolicitante = $_POST['dniSolicitante'];
            $solicitanteNombre = $_POST['solicitanteNombre'];
            $sscc = $_POST['sscc'];
            $nombreProyecto = $_POST['nombreProyecto'];
            $motivoAnticipo = $_POST['motivoAnticipo'];
            $montoTotal = $_POST['montoTotal'];

            $latestEstado = $this->anticipoModel->getLatestAnticipoEstado($id);
            if ($latestEstado === null) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No se pudo verificar el estado del anticipo']);
                exit;
            }

            // Validar que el estado sea "Nuevo" u "Observado"
            if (!in_array($latestEstado, ['Nuevo', 'Observado'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'El anticipo no puede ser autorizado. Solo se pueden autorizar anticipos en estado "Nuevo" u "Observado".']);
                exit;
            }

            if ($this->anticipoModel->updateAnticipoEstado($id, 'Autorizado', $_SESSION['id'], 'Autorizado')) {
                // $_SESSION['success'] = 'Anticipo aprobado correctamente.';

                // error_log($_SESSION['trabajador']['correo']);
                $correo_aprobador = $_SESSION['trabajador']['correo'];

                //url usada en el correo
                $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/anticipos';

                $solicitante = $this->trabajadorModel->getTrabajadorByDni($dniSolicitante);
                $correo_solicitante = $solicitante['correo'];

                $correos_cc = [$correo_solicitante];

                if ($correo_solicitante) {

                    $to = $correo_aprobador;
                    $subject = "SIAR - TECHING - Anticipo N° $id ha sido autorizado";

                    // obteniendo información de los tesoreros
                    $dnisRol5 = $this->trabajadorModel->getDnisByRol5();
                    $rol5Correos = [];
                    if (!empty($dnisRol5)) {
                        // Buscar correos en tb_trabajadores (base externa) para los DNI con rol 5
                        foreach ($dnisRol5 as $dni) {
                            $trabajador = $this->trabajadorModel->getTrabajadorByDni($dni);
                            if ($trabajador && isset($trabajador['correo'])) {
                                $rol5Correos[] = $trabajador['correo'];
                            }
                        }
                    }

                    $body = "
                        <h2>Notificación de Anticipo</h2>
                        <p>Se realizó la primera autorización del anticipo correctamente. Información del anticipo:</p>
                        <ul>
                            <li><strong>N° de Anticipo:</strong> $id</li>
                            <li><strong>Motivo:</strong> $motivoAnticipo</li>
                            <li><strong>DNI Solicitante:</strong> $dniSolicitante</li>
                            <li><strong>Nombre Solicitante: $solicitanteNombre</strong></li>
                            <li><strong>Sub sub-centro de costo:</strong> $sscc</li>
                            <li><strong>Nombre del Proyecto:</strong> $nombreProyecto</li>
                            <li><strong>Monto:</strong> $montoTotal</li>
                        </ul>
                        <p>Recuerde que el anticipo deberá ser autorizado por el área de <b>contabilidad</b> para continuar con la atención de su solicitud.</p>
                        <hr>
                        <br>
                        <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles del anticipo.</p>
                        <a href='{$url_plataforma}'>SIAR - TECHING</a>
                    ";

                    error_log("Correo del solciitante" .$correo_solicitante);

                    if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $correos_cc)) {
                        error_log("Anticipo creado y notificación enviada");
                    } else {
                        error_log("No se pudo enviar la notificación");
                    }

                    // Enviar correo a usuarios con rol 5
                    if (!empty($rol5Correos)) {
                        $bodyTesoreria = "
                            <h2>Notificación de Anticipo</h2>
                            <p>El usuario aprobador realizó la primera autorización correctamente. Información del anticipo:</p>
                            <ul>
                                <li><strong>N° de Anticipo:</strong> $id</li>
                                <li><strong>Motivo:</strong> $motivoAnticipo</li>
                                <li><strong>DNI Solicitante:</strong> $dniSolicitante</li>
                                <li><strong>Nombre Solicitante: $solicitanteNombre</strong></li>
                                <li><strong>Sub sub-centro de costo:</strong> $sscc</li>
                                <li><strong>Nombre del Proyecto:</strong> $nombreProyecto</li>
                                <li><strong>Monto:</strong> $montoTotal</li>
                            </ul>
                            <p>Deberá revisar en la plataforma SIAR el anticipo respectivo para poder realizar la segunda autorización o marcarlo como observado en caso exista un dato incorrecto.</p>
                            <hr>
                            <br>
                            <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles del anticipo.</p>
                            <a href='{$url_plataforma}'>SIAR - TECHING</a>
                        ";
                        foreach ($rol5Correos as $rol5Correo) {
                            if ($this->emailConfig->sendSiarNotification($rol5Correo, $subject, $bodyTesoreria, [])) {
                                error_log("Notificación enviada al usuario con rol 5: $rol5Correo");
                            } else {
                                error_log("No se pudo enviar la notificación al usuario con rol 5: $rol5Correo");
                            }
                        }
                    } else {
                        error_log("No se encontraron correos de usuarios con rol 5");
                    }
                } else {
                    error_log("No se envió el correo, no se encontró el correo del solicitante");
                }
            


                header('Content-Type: application/json');
                echo json_encode(['success' => 'Anticipo autorizado correctamente.']);
                exit;
            } else {
                // $_SESSION['error'] = 'Error al autorizar el anticipo.';
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No se pudo autorizar el anticipo']);
                exit;
            }
        }
        header("Location: /".$_SESSION['ruta_base']."/anticipos");
        exit;
    }

    //Funcionalidad para autorizar totalmente un anticipo y enviar notificación
    public function autorizarTotalmente() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 5) {
            error_log( 'No tiene permisos para realizar este tipo de aprobación');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tiene permisos para realizar este tipo de aprobación']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];

            // Elementos enviados para la notificación por correo
            $dniSolicitante = $_POST['dniSolicitante'];
            $solicitanteNombre = $_POST['solicitanteNombre'];
            $sscc = $_POST['sscc'];
            $nombreProyecto = $_POST['nombreProyecto'];
            $motivoAnticipo = $_POST['motivoAnticipo'];
            $montoTotal = $_POST['montoTotal'];

            $latestEstado = $this->anticipoModel->getLatestAnticipoEstado($id);
            if ($latestEstado === null) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No se pudo verificar el estado del anticipo']);
                exit;
            }

            // Validar que el estado sea "autorizado"
            if (!in_array($latestEstado, ['Autorizado'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'El anticipo no puede ser autorizado. Solo se pueden autorizar totalmente anticipos en estado "Autorizado".']);
                exit;
            }

            if ($this->anticipoModel->updateAnticipoEstado($id, 'Autorizado Totalmente', $_SESSION['id'], 'Autorizado Totalmente')) {
                // $_SESSION['success'] = 'Anticipo aprobado correctamente.';

                $correo_tesorero = $_SESSION['trabajador']['correo'];

                //url usada en el correo
                $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/anticipos';

                $solicitante = $this->trabajadorModel->getTrabajadorByDni($dniSolicitante);
                $correo_solicitante = $solicitante['correo'];

                $correos_cc = [$correo_solicitante];

                if ($correo_solicitante) {

                    $to = $correo_tesorero;
                    $subject = "SIAR - TECHING - Anticipo N° $id ha sido autorizado totalmente";

                    $body = "
                        <h2>Notificación de Anticipo</h2>
                        <p>Se realizó la segunda autorización del anticipo correctamente. Información del anticipo:</p>
                        <ul>
                            <li><strong>N° de Anticipo:</strong> $id</li>
                            <li><strong>Motivo:</strong> $motivoAnticipo</li>
                            <li><strong>DNI Solicitante:</strong> $dniSolicitante</li>
                            <li><strong>Nombre Solicitante: $solicitanteNombre</strong></li>
                            <li><strong>Sub sub-centro de costo:</strong> $sscc</li>
                            <li><strong>Nombre del Proyecto:</strong> $nombreProyecto</li>
                            <li><strong>Monto:</strong> $montoTotal</li>
                        </ul>
                        <p>Por este medio, recibirá una notificación de cuando su anticipo se encuentre abonado.</p>
                        <hr>
                        <br>
                        <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles del anticipo.</p>
                        <a href='{$url_plataforma}'>SIAR - TECHING</a>
                    ";

                    error_log("Correo del solciitante" .$correo_solicitante);

                    if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $correos_cc)) {
                        error_log("Anticipo creado y notificación enviada");
                    } else {
                        error_log("No se pudo enviar la notificación");
                    }

                    // Enviar correo a usuarios con rol 5
                    if (!empty($rol5Correos)) {
                        $bodyTesoreria = "
                            <h2>Notificación de Anticipo</h2>
                            <p>El usuario aprobador realizó la primera autorización correctamente. Información del anticipo:</p>
                            <ul>
                                <li><strong>N° de Anticipo:</strong> $id</li>
                                <li><strong>Motivo:</strong> $motivoAnticipo</li>
                                <li><strong>DNI Solicitante:</strong> $dniSolicitante</li>
                                <li><strong>Nombre Solicitante: $solicitanteNombre</strong></li>
                                <li><strong>Sub sub-centro de costo:</strong> $sscc</li>
                                <li><strong>Nombre del Proyecto:</strong> $nombreProyecto</li>
                                <li><strong>Monto:</strong> $montoTotal</li>
                            </ul>
                            <p>Deberá revisar en la plataforma SIAR el anticipo respectivo para poder realizar la segunda autorización o marcarlo como observado en caso exista un dato incorrecto.</p>
                            <hr>
                            <br>
                            <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles del anticipo.</p>
                            <a href='{$url_plataforma}'>SIAR - TECHING</a>
                        ";
                        foreach ($rol5Correos as $rol5Correo) {
                            if ($this->emailConfig->sendSiarNotification($rol5Correo, $subject, $bodyTesoreria, [])) {
                                error_log("Notificación enviada al usuario con rol 5: $rol5Correo");
                            } else {
                                error_log("No se pudo enviar la notificación al usuario con rol 5: $rol5Correo");
                            }
                        }
                    } else {
                        error_log("No se encontraron correos de usuarios con rol 5");
                    }
                } else {
                    error_log("No se envió el correo, no se encontró el correo del solicitante");
                }
                
    
                header('Content-Type: application/json');
                echo json_encode(['success' => 'Anticipo autorizado totalmente.']);
                exit;
            } else {
                // $_SESSION['error'] = 'Error al aprobar el anticipo.';
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No se pudo autorizar el anticipo']);
                exit;
            }
        }
        header("Location: /".$_SESSION['ruta_base']."/anticipos");
        exit;
    }

    //  Funcionalidad para marcar como observado un anticipo y enviar notificación
    public function observarAnticipo() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 5) {
            error_log( 'No tiene permisos para realizar este tipo de actividad');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tiene permisos para realizar este tipo de actividad']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];

            // Elementos enviados para la notificación por correo
            $dniSolicitante = $_POST['dniSolicitante'];
            $solicitanteNombre = $_POST['solicitanteNombre'];
            $sscc = $_POST['sscc'];
            $nombreProyecto = $_POST['nombreProyecto'];
            $motivoAnticipo = $_POST['motivoAnticipo'];
            $montoTotal = $_POST['montoTotal'];

            $comentario = trim($_POST['comentario'] ?? 'Anticipo Observado');

            $latestEstado = $this->anticipoModel->getLatestAnticipoEstado($id);
            error_log("Resultado de latestEstado:");
            error_log($latestEstado);
            // $ultimoAutorizador = $latestEstado['id_usuario'];

            if ($latestEstado === null) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No se pudo verificar el estado del anticipo']);
                exit;
            }

            // Validar que el estado actual sea autorizado
            if (!in_array($latestEstado, ['Autorizado'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'El anticipo no pudo ser marcado como observado. Solo se pueden observar anticipos en estado "Autorizado".']);
                exit;
            }

            if ($this->anticipoModel->updateAnticipoEstado($id, 'Observado', $_SESSION['id'], $comentario)) {

                $idAutorizador = $this->anticipoModel->getLastAuthorizerId($id);
                error_log("Id del autorizador: ".$idAutorizador);

                $dniAutorizador = $this->trabajadorModel->getDniById($idAutorizador);
                error_log("DNI del autorizador: ".$dniAutorizador);

                // Obtener el correo del autorizador
                $autorizador = $idAutorizador ? $this->trabajadorModel->getTrabajadorByDni($dniAutorizador) : null;
                $correo_autorizador = $autorizador && isset($autorizador['correo']) ? $autorizador['correo'] : null;
                error_log("Correo del autorizador". $correo_autorizador);
                
                // Se obtiene el correo del tesorero para la notificación
                $correo_tesorero = $_SESSION['trabajador']['correo'];

                //url usada en el correo
                $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/anticipos';

                $solicitante = $this->trabajadorModel->getTrabajadorByDni($dniSolicitante);
                $correo_solicitante = $solicitante['correo'];

                $correos_cc = [$correo_solicitante];
                if ($correo_autorizador) {
                    $correos_cc[] = $correo_autorizador; // Añadir el correo del autorizador a CC
                }

                if ($correo_solicitante) {

                    $to = $correo_tesorero;
                    $subject = "SIAR - TECHING - Anticipo N° $id ha sido marcado como observado";

                    $body = "
                        <h2>Notificación de Anticipo</h2>
                        <p>El anticipo fue marcado como observado, favor de revisar las observaciones para que puedan ser corregidas. Información del anticipo:</p>
                        <ul>
                            <li><strong>N° de Anticipo:</strong> $id</li>
                            <li><strong>Motivo:</strong> $motivoAnticipo</li>
                            <li><strong>DNI Solicitante:</strong> $dniSolicitante</li>
                            <li><strong>Nombre Solicitante: $solicitanteNombre</strong></li>
                            <li><strong>Sub sub-centro de costo:</strong> $sscc</li>
                            <li><strong>Nombre del Proyecto:</strong> $nombreProyecto</li>
                            <li><strong>Monto:</strong> $montoTotal</li>
                            <p><strong>Observación:</strong> $comentario</p>
                        </ul>
                        <hr>
                        <br>
                        <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles del anticipo.</p>
                        <a href='{$url_plataforma}'>SIAR - TECHING</a>
                    ";

                    error_log("Correo del solciitante" .$correo_solicitante);

                    if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $correos_cc)) {
                        error_log("Anticipo marcado como observado y con notificación enviada");
                        } else {
                            error_log("No se pudo enviar la notificación");
                        }

                    } else {
                        error_log("No se envió el correo, no se encontró el correo del solicitante");
                    }

                header('Content-Type: application/json');
                echo json_encode(['success' => 'Anticipo marcado como observado.']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'El anticipo no pudo ser marcado como observado.']);
                exit;
            }
        }
        header("Location: /".$_SESSION['ruta_base']."/anticipos");
        exit;
    }

   public function abonarAnticipo() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 5) {
            error_log('No tiene permisos para realizar este tipo de actividad');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tiene permisos para realizar este tipo de actividad']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];

            // Elementos enviados para la notificación por correo
            $dniSolicitante = $_POST['dniSolicitante'];
            $solicitanteNombre = $_POST['solicitanteNombre'];
            $sscc = $_POST['sscc'];
            $nombreProyecto = $_POST['nombreProyecto'];
            $motivoAnticipo = $_POST['motivoAnticipo'];
            $montoTotal = $_POST['montoTotal'];
            $comentario = trim($_POST['comentario'] ?? 'Anticipo Abonado');

            $latestEstado = $this->anticipoModel->getLatestAnticipoEstado($id);
            if ($latestEstado === null) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'No se pudo verificar el estado del anticipo']);
                exit;
            }

            // Validar que el estado actual sea autorizado
            if (!in_array($latestEstado, ['Autorizado Totalmente'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'El anticipo no puede ser marcado como Abonado. El estado actual deberá ser "Autorizado Totalmente"']);
                exit;
            }

            if ($this->anticipoModel->abonarAnticipo($id, $_SESSION['id'], $comentario)) {

                $correo_tesorero = $_SESSION['trabajador']['correo'];

                //url usada en el correo
                $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/anticipos';

                $solicitante = $this->trabajadorModel->getTrabajadorByDni($dniSolicitante);
                $correo_solicitante = $solicitante['correo'];

                $correos_cc = [$correo_solicitante];

                if ($correo_solicitante) {

                    $to = $correo_tesorero;
                    $subject = "SIAR - TECHING - Anticipo N° $id abonado";

                    $body = "
                        <h2>Notificación de Anticipo</h2>
                        <p>Se procedió a realizar el abono del anticipo.</p>
                        <ul>
                            <li><strong>N° de Anticipo:</strong> $id</li>
                            <li><strong>Motivo:</strong> $motivoAnticipo</li>
                            <li><strong>DNI Solicitante:</strong> $dniSolicitante</li>
                            <li><strong>Nombre Solicitante: $solicitanteNombre</strong></li>
                            <li><strong>Sub sub-centro de costo:</strong> $sscc</li>
                            <li><strong>Nombre del Proyecto:</strong> $nombreProyecto</li>
                            <li><strong>Monto:</strong> $montoTotal</li>
                        </ul>
                        <p>Recuerde que deberá rendir este anticipo en el panel de 'Rendiciones' dentro de la plataforma SIAR, antes de la fecha de rendición estimada.</p>
                        <hr>
                        <br>
                        <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles del anticipo.</p>
                        <a href='{$url_plataforma}'>SIAR - TECHING</a>
                    ";

                    error_log("Correo del solciitante" .$correo_solicitante);

                    if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $correos_cc)) {
                        error_log("Anticipo creado y notificación enviada");
                    } else {
                        error_log("No se pudo enviar la notificación");
                    }

                    // Enviar correo a usuarios con rol 5
                    if (!empty($rol5Correos)) {
                        $bodyTesoreria = "
                            <h2>Notificación de Anticipo</h2>
                            <p>El usuario aprobador realizó la primera autorización correctamente. Información del anticipo:</p>
                            <ul>
                                <li><strong>N° de Anticipo:</strong> $id</li>
                                <li><strong>Motivo:</strong> $motivoAnticipo</li>
                                <li><strong>DNI Solicitante:</strong> $dniSolicitante</li>
                                <li><strong>Nombre Solicitante: $solicitanteNombre</strong></li>
                                <li><strong>Sub sub-centro de costo:</strong> $sscc</li>
                                <li><strong>Nombre del Proyecto:</strong> $nombreProyecto</li>
                                <li><strong>Monto:</strong> $montoTotal</li>
                            </ul>
                            <p>Deberá revisar en la plataforma SIAR el anticipo respectivo para poder realizar la segunda autorización o marcarlo como observado en caso exista un dato incorrecto.</p>
                            <hr>
                            <br>
                            <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles del anticipo.</p>
                            <a href='{$url_plataforma}'>SIAR - TECHING</a>
                        ";
                        foreach ($rol5Correos as $rol5Correo) {
                            if ($this->emailConfig->sendSiarNotification($rol5Correo, $subject, $bodyTesoreria, [])) {
                                error_log("Notificación enviada al usuario con rol 5: $rol5Correo");
                            } else {
                                error_log("No se pudo enviar la notificación al usuario con rol 5: $rol5Correo");
                            }
                        }
                    } else {
                        error_log("No se encontraron correos de usuarios con rol 5");
                    }
                } else {
                    error_log("No se envió el correo, no se encontró el correo del solicitante");
                }

                header('Content-Type: application/json');
                echo json_encode(['success' => 'Anticipo abonado exitosamente.']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'El anticipo no pudo ser abonado.']);
                exit;
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['error' => 'Solicitud inválida.']);
        exit;
    }

    /* Cargar anticipos */
    // Endpoint para obtener detalles de un anticipo
    public function getAnticipoDetails() {
        header('Content-Type: application/json');
        if (isset($_GET['id_anticipo'])) {
            $id_anticipo = $_GET['id_anticipo'];
            $anticipo = $this->anticipoModel->getAnticipoById($id_anticipo);
            if ($anticipo) {
                echo json_encode($anticipo);
            } else {
                echo json_encode(['error' => 'Anticipo no encontrado']);
            }
        } else {
            echo json_encode(['error' => 'No se proporcionó id_anticipo']);
        }
        exit;
    }
    
    public function detallesViaticos() {
        $id_anticipo = $_GET['id_anticipo'];
        $info_anticipo = $this->anticipoModel->getDetallesViaticosByAnticipo($id_anticipo);
        require_once 'src/views/anticipos_detalles_viaticos.php';
    }
}
?>