<?php
require_once 'src/config/Database.php';
require_once 'src/models/AnticipoModel.php';

class AnticipoController {
    private $db;
    private $anticipoModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->anticipoModel = new AnticipoModel();
    }

    public function index() {
        if (!isset($_SESSION['id'])) {
            header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
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
            $detalles_gastos = isset($_POST['detalles_gastos']) ? $_POST['detalles_gastos'] : [];//here
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
                            'tipo_transporte' => $_POST["tipo-transporte-$index-$subindex"] ?? 'terrestre',
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
            } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $nombre_proyecto)) {
                $response['message'] = 'El nombre del proyecto solo puede contener letras, números y espacios.';
                error_log($response['message']);
            } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $motivo_anticipo)) {
                $response['message'] = 'El motivo del anticipo solo puede contener letras, números y espacios.';
                error_log($response['message']);
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_solicitud) || !strtotime($fecha_solicitud)) {
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
                        if (!preg_match('/^[a-zA-Z0-9\sáéíóúÁÉÍÓÚñÑ]+$/', $detalle['motivo'])) {
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
                        if (empty($persona['nombre_persona']) || !preg_match('/^[a-zA-Z\sáéíóúÁÉÍÓÚñÑ]+$/', $persona['nombre_persona'])) {
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
                    if ($this->anticipoModel->anticipoPendiente($id_usuario)) {
                    $response['message'] = 'El solicitante aún tiene pendiente un anticipo.';
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
                            if ($this->anticipoModel->addAnticipo($id_usuario, $solicitante, $solicitante_nombres, $dni_solicitante, $departamento, $departamento_nombre, $codigo_sscc, $cargo, $nombre_proyecto, $fecha_solicitud, $motivo_anticipo, $monto_total_solicitado, $id_cat_documento, $detalles_gastos, $detalles_viajes)) {
                                $response['success'] = true;
                                $response['message'] = 'Anticipo registrado con éxito';
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

    public function update() {
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
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => $result['error'] ?? 'Error desconocido']);
            }
        } else {
            echo json_encode(['error' => 'Método no permitido']);
        }
    }

    public function approve() {
        session_start();
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
            $_SESSION['error'] = 'No tienes permiso para aprobar anticipos.';
            header('Location: /proy_anticipos_rendiciones/anticipos');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $comentario = trim($_POST['comentario'] ?? 'Anticipo aprobado');
            if ($this->anticipoModel->updateAnticipoEstado($id, 'Aprobado', $_SESSION['id'], $comentario)) {
                $_SESSION['success'] = 'Anticipo aprobado correctamente.';
            } else {
                $_SESSION['error'] = 'Error al aprobar el anticipo.';
            }
        }
        header('Location: /proy_anticipos_rendiciones/anticipos');
        exit;
    }

    //  Funcionalidad para rechazar un anticipo
    public function reject() {
        session_start();
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
            $_SESSION['error'] = 'No tienes permiso para rechazar anticipos.';
            header('Location: /proy_anticipos_rendiciones/anticipos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $comentario = trim($_POST['comentario'] ?? 'Anticipo rechazado');
            if ($this->anticipoModel->updateAnticipoEstado($id, 'Rechazado', $_SESSION['id'], $comentario)) {
                $_SESSION['success'] = 'Anticipo rechazado correctamente.';
            } else {
                $_SESSION['error'] = 'Error al rechazar el anticipo.';
            }
        }
        header('Location: /proy_anticipos_rendiciones/anticipos');
        exit;
    }

    /*Cargar anticipos here*/
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

}
?>