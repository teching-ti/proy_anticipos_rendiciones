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
            header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
            exit;
        }

        $sccs = $this->anticipoModel->getAllScc();
        $ssccs2 = $this->anticipoModel->getAllSscc();
        

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

            //error_log("Datos id: $codigo_sscc, proyecto: $nombre_proyecto, fecha: $fecha_solicitud, motivo: $motivo_anticipo, monto: $monto_total_solicitado, cat: $id_cat_documento, detalles_gastos=" .json_encode($detalles_gastos));

            // Validaciones
            if (empty($codigo_sscc) || empty($nombre_proyecto) || empty($fecha_solicitud) || empty($motivo_anticipo) || $monto_total_solicitado <= 0 || !$id_cat_documento) {
                $_SESSION['error'] = 'Los campos sub-subcentro, proyecto, fecha, motivo y monto son obligatorios. El monto debe ser mayor a 0.';
                error_log($_SESSION['error']);
            } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $nombre_proyecto)) {
                $_SESSION['error'] = 'El nombre del proyecto solo puede contener letras, números y espacios.';
                error_log($_SESSION['error']);
            } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $motivo_anticipo)) {
                $_SESSION['error'] = 'El motivo del anticipo solo puede contener letras, números y espacios.';
                error_log($_SESSION['error']);
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_solicitud) || !strtotime($fecha_solicitud)) {
                $_SESSION['error'] = 'La fecha de solicitud debe tener el formato YYYY-MM-DD.';
                error_log($_SESSION['error']);
            } else {
                if ($concepto == 'compras-menores' && !empty($detalles_gastos)) {
                    // Validar detalles de gastos menores
                    foreach ($detalles_gastos as $index => $detalle) {
                        if (empty($detalle['descripcion']) || empty($detalle['motivo']) || !isset($detalle['importe']) || empty($detalle['moneda'])) {
                            $_SESSION['error'] = "El detalle de gasto #$index tiene campos incompletos.";
                            error_log($_SESSION['error']);
                            header('Location: /proy_anticipos_rendiciones/anticipos');
                            exit;
                        }
                        if (!preg_match('/^[a-zA-Z0-9\sáéíóúÁÉÍÓÚñÑ]+$/', $detalle['motivo'])) {
                            $_SESSION['error'] = "El motivo del detalle de gasto #$index contiene caracteres no permitidos.";
                            error_log($_SESSION['error']);
                            header('Location: /proy_anticipos_rendiciones/anticipos');
                            exit;
                        }
                    }
                }

                // Validar detalles de viajes
                if ($concepto === 'viajes' && !empty($detalles_viajes)) {
                    foreach ($detalles_viajes as $index => $persona) {
                        error_log("Validando persona de viaje #$index: " . json_encode($persona));
                        if (empty($persona['doc_identidad']) || !preg_match('/^[0-9A-Za-z]{1,11}$/', $persona['doc_identidad'])) {
                            $_SESSION['error'] = "El documento de identidad de la persona #$index es inválido.";
                            error_log($_SESSION['error']);
                            header('Location: /proy_anticipos_rendiciones/anticipos');
                            exit;
                        }
                        if (empty($persona['nombre_persona']) || !preg_match('/^[a-zA-Z\sáéíóúÁÉÍÓÚñÑ]+$/', $persona['nombre_persona'])) {
                            $_SESSION['error'] = "El nombre de la persona #$index es inválido.";
                            error_log($_SESSION['error']);
                            header('Location: /proy_anticipos_rendiciones/anticipos');
                            exit;
                        }
                        if (empty($persona['id_cargo']) || !$this->anticipoModel->cargoExists($persona['id_cargo'])) {
                            $_SESSION['error'] = "El cargo de la persona #$index es inválido.";
                            error_log($_SESSION['error']);
                            header('Location: /proy_anticipos_rendiciones/anticipos');
                            exit;
                        }
                        foreach ($persona['viaticos'] as $viatico) {
                            if ($viatico['dias'] < 0 || $viatico['monto'] < 0) {
                                $_SESSION['error'] = "Los días y el monto de viáticos para la persona #$index deben ser no negativos.";
                                error_log($_SESSION['error']);
                                header('Location: /proy_anticipos_rendiciones/anticipos');
                                exit;
                            }
                            // Validar monto contra tb_tarifario
                            $tarifa = $this->anticipoModel->getTarifaByCargoAndConcepto($persona['id_cargo'], $viatico['id_concepto']);
                            if ($tarifa && abs($viatico['monto'] - ($viatico['dias'] * $tarifa)) > 0.01) {
                                $_SESSION['error'] = "El monto de viático (concepto ID {$viatico['id_concepto']}) para la persona #$index no coincide con la tarifa oficial.";
                                error_log($_SESSION['error']);
                                header('Location: /proy_anticipos_rendiciones/anticipos');
                                exit;
                            }
                        }
                        foreach ($persona['transporte'] as $subindex => $transp) {
                            if (empty($transp['tipo_transporte']) || !in_array($transp['tipo_transporte'], ['terrestre', 'aereo'])) {
                                $_SESSION['error'] = "El tipo de transporte #$subindex para la persona #$index es inválido.";
                                error_log($_SESSION['error']);
                                header('Location: /proy_anticipos_rendiciones/anticipos');
                                exit;
                            }
                            if (empty($transp['ciudad_origen']) || empty($transp['ciudad_destino']) || !preg_match('/^[a-zA-Z\sáéíóúÁÉÍÓÚñÑ]+$/', $transp['ciudad_origen']) || !preg_match('/^[a-zA-Z\sáéíóúÁÉÍÓÚñÑ]+$/', $transp['ciudad_destino'])) {
                                $_SESSION['error'] = "Las ciudades de transporte #$subindex para la persona #$index son inválidas.";
                                error_log($_SESSION['error']);
                                header('Location: /proy_anticipos_rendiciones/anticipos');
                                exit;
                            }
                            if (empty($transp['fecha']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $transp['fecha'])) {
                                $_SESSION['error'] = "La fecha de transporte #$subindex para la persona #$index es inválida.";
                                error_log($_SESSION['error']);
                                header('Location: /proy_anticipos_rendiciones/anticipos');
                                exit;
                            }
                            if ($transp['monto'] <= 0) {
                                $_SESSION['error'] = "El monto de transporte #$subindex para la persona #$index debe ser mayor a 0.";
                                error_log($_SESSION['error']);
                                header('Location: /proy_anticipos_rendiciones/anticipos');
                                exit;
                            }
                        }
                    }
                }

                // Validar monto total
                // $suma_total = 0;
                // if ($concepto === 'compras-menores') {
                //     $suma_total = array_sum(array_column($detalles_gastos, 'importe'));
                // } elseif ($concepto === 'viajes') {
                //     foreach ($detalles_viajes as $persona) {
                //         foreach ($persona['viaticos'] as $viatico) {
                //             $suma_total += $viatico['monto'];
                //         }
                //         foreach ($persona['transporte'] as $transp) {
                //             $suma_total += $transp['monto'];
                //         }
                //     }
                // }
                // if (abs($monto_total_solicitado - $suma_total) > 0.01) {
                //     $_SESSION['error'] = "El monto total ($monto_total_solicitado) no coincide con la suma de los detalles ($suma_total).";
                //     error_log($_SESSION['error']);
                //     header('Location: /proy_anticipos_rendiciones/anticipos');
                //     exit;
                // }

                // Verificar relaciones
                // $sscc_exists = false;
                // error_log("Mostrando los ssccs");

                // foreach ($ssccs2 as $s) {
                //     error_log("El SSCC del formulario es: $codigo_sscc");
                //     if ($s['codigo'] === $codigo_sscc) {
                //         error_log("El SSCC con el que s compara es: ".$s['codigo']);
                //         $sscc_exists = true;
                //         break;
                //     }
                // }

                // if (!$sscc_exists) {
                //     $_SESSION['error'] = 'Sub-subcentro de costo inválido.';
                //     error_log($_SESSION['error']);
                // } 

                if ($this->anticipoModel->anticipoPendiente($id_usuario)) {
                    $_SESSION['error'] = 'El solicitante aún tiene pendiente un anticipo.';
                    error_log($_SESSION['error']);
                } else {
                    // Verificar saldo disponible
                    $saldo_disponible = $this->anticipoModel->getSaldoDisponibleBySscc($codigo_sscc);
                    if ($saldo_disponible === null) {
                        $_SESSION['error'] = 'No se encontró un presupuesto activo para el sub-subcentro seleccionado.';
                        error_log($_SESSION['error']);
                    } elseif ($monto_total_solicitado > $saldo_disponible) {
                        $_SESSION['error'] = "No se registró el anticipo. El monto solicitado ($monto_total_solicitado) excede el saldo disponible ($saldo_disponible) para el sub-subcentro.";
                        error_log($_SESSION['error']);
                    } else {
                        if ($this->anticipoModel->addAnticipo($id_usuario, $solicitante, $solicitante_nombres, $dni_solicitante, $departamento, $departamento_nombre, $codigo_sscc, $cargo, $nombre_proyecto, $fecha_solicitud, $motivo_anticipo, $monto_total_solicitado, $id_cat_documento, $detalles_gastos, $detalles_viajes)) {
                            $_SESSION['success'] = 'Anticipo registrado correctamente.';
                            error_log('Anticipo registrado con éxito');
                        } else {
                            $_SESSION['error'] = 'Error al registrar el anticipo.';
                            error_log($_SESSION['error']);
                        }
                    }
                }
            }
            header('Location: /proy_anticipos_rendiciones/anticipos');
            exit;
        }
        require_once 'src/views/anticipos.php';
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