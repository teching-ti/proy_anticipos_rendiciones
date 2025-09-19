<?php
require_once 'src/config/Database.php';
require_once 'src/models/TrabajadorModel.php';
require_once 'src/models/AnticipoModel.php';
require_once 'src/config/EmailConfig.php';
require_once 'src/config/AutorizationDocumentConfig.php';

class AnticipoController {
    private $db;
    private $anticipoModel;
    private $emailConfig;
    private $logoBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAH4AAAApCAYAAADzqJ3HAAAA8HpUWHRSYXcgcHJvZmlsZSB0eXBlIGV4aWYAAHjajVFZbsUgDPznFD2CNxYfh2xSb/CO/ybBvDSVKtUSxh4bMwxpf30f6es0aZIs11a8FIKZm0tH0GjYcnkmu/xldY8aP/EkHgUBpNh1pC6B78ARc+Qel/Dsn4NmwB1Rvgu9B7488SUGSvs9KBgoj5tpiwMxSCUY2cjXYFS81cfTtpWe1u5lWqXkwtXgTajW4oibkFXouZ1Ej3WIk/IQ9APMfLYKOMmurASvWgZLPZdpx57hUU1oZIAn5PCicglP+EpQAPP4jKPTR8yf2twa/WH/eVZ6A6DCdu3Vw4HaAAABhGlDQ1BJQ0MgcHJvZmlsZQAAeJx9kT1Iw0AcxV/TlmqpdLCDiEOG6mQXFXEsVSyChdJWaNXB5NIvaNKSpLg4Cq4FBz8Wqw4uzro6uAqC4AeIs4OToouU+L+k0CLGg+N+vLv3uHsHCO0aUw1fHFA1U88kE2K+sCoGXuFHEGH4MCgxo5HKLubgOr7u4eHrXYxnuZ/7cwwpRYMBHpE4zhq6SbxBPLtpNjjvE0dYRVKIz4kndbog8SPXZYffOJdtFnhmRM9l5okjxGK5j+U+ZhVdJZ4hjiqqRvlC3mGF8xZntdZk3XvyF4aK2kqW6zTHkMQSUkhDhIwmqqjBRIxWjRQDGdpPuPhHbX+aXDK5qmDkWEAdKiTbD/4Hv7s1StNTTlIoAfhfLOtjHAjsAp2WZX0fW1bnBPA+A1daz19vA3OfpLd6WvQICG8DF9c9Td4DLneAkaeGpEu25KUplErA+xl9UwEYvgWCa05v3X2cPgA56mr5Bjg4BCbKlL3u8u6B/t7+PdPt7wcpY3KJn+OU0AAADXZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IlhNUCBDb3JlIDQuNC4wLUV4aXYyIj4KIDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+CiAgPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIKICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIKICAgIHhtbG5zOkdJTVA9Imh0dHA6Ly93d3cuZ2ltcC5vcmcveG1wLyIKICAgIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIgogICAgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIgogICB4bXBNTTpEb2N1bWVudElEPSJnaW1wOmRvY2lkOmdpbXA6Y2VkODNkNjUtNGY4ZC00MjFmLTkyOTItNDk2N2M2Nzc3YWNiIgogICB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjkyYTE3YTMzLWY3NGQtNDliMy1hZDQ0LWY4ZWZhMjhkYzQ0YyIKICAgeG1wTU06T3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOjA1MjllZWRmLTFiZGUtNGM5OS1iOGUxLTZiOTk2NjIyMzNjZCIKICAgZGM6Rm9ybWF0PSJpbWFnZS9wbmciCiAgIEdJTVA6QVBJPSIyLjAiCiAgIEdJTVA6UGxhdGZvcm09IldpbmRvd3MiCiAgIEdJTVA6VGltZVN0YW1wPSIxNzE1MzYwOTY0MDAwNDAwIgogICBHSU1QOlZlcnNpb249IjIuMTAuMzYiCiAgIHRpZmY6T3JpZW50YXRpb249IjEiCiAgIHhtcDpDcmVhdG9yVG9vbD0iR0lNUCAyLjEwIgogICB4bXA6TWV0YWRhdGFEYXRlPSIyMDI0OjA1OjEwVDEyOjA5OjIyLTA1OjAwIgogICB4bXA6TW9kaWZ5RGF0ZT0iMjAyNDowNToxMFQxMjowOToyMi0wNTowMCI+CiAgIDx4bXBNTTpIaXN0b3J5PgogICAgPHJkZjpTZXE+CiAgICAgPHJkZjpsaQogICAgICBzdEV2dDphY3Rpb249InNhdmVkIgogICAgICBzdEV2dDpjaGFuZ2VkPSIvIgogICAgICBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOmZkMGQ5ODdjLTY4MDgtNGViNi1hYjA5LTRlOTJkMTI0NDM3ZiIKICAgICAgc3RFdnQ6c29mdHdhcmVBZ2VudD0iR2ltcCAyLjEwIChXaW5kb3dzKSIKICAgICAgc3RFdnQ6d2hlbj0iMjAyNC0wNS0xMFQxMjowOToyMyIvPgogICAgPC9yZGY6U2VxPgogICA8L3htcE1NOkhpc3Rvcnk+CiAgPC9yZGY6RGVzY3JpcHRpb24+CiA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9InciPz48TZWsAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAC4jAAAuIwF4pT92AAAAB3RJTUUH6AUKEQkX3murkgAAABl0RVh0Q29tbWVudABDcmVhdGVkIHdpdGggR0lNUFeBDhcAAA+XSURBVHja7Zt5dFX1tcc/+9ybBDIwCQgBhUVQZlEeg7W2opQCySUDiuDAJPbhwLJqFZ+KONBqZ8Q+7bMViNBaFc14AxQVHKiKICQMKiBjGASEEMhA7vDb7497Qi7h3swVoey1zlrJub9zfsN3D9+9f78jXJD6SdqKZlA+AFUXMACIBnaDuPFLHrmJJefCNOQCkrXL/I04LYNTLcQCUDEZb86TnM3tf6rKA8BAIAZYjSGVHNfBC8Cfg/J/62KlmbOkJRrZCfFcDtIDtJtCtL1gRSAbQD9dsfzlPQtXdboBeAroDfIiwmNkJpV9n+fo/I9ENnXJRai5HEsSUO0AGIRvMGzD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';

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

    public function getAnticipoPendiente(){
        header('Content-Type: application/json');

        $id_usuario = $_SESSION['id'];
        $anticipo_pendiente = $this->anticipoModel->anticipoPendiente($id_usuario);
        echo json_encode($anticipo_pendiente);
        return;
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
            $fecha_inicio = trim($_POST['fecha_ejecucion'] ?? ''); // Fecha inicio del gasto
            $fecha_fin = trim($_POST['fecha_finalizacion'] ?? ''); // Fecha fin del gasto
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
            } elseif(empty($fecha_inicio) && empty($fecha_fin)){
                $response['message'] = 'Los campos de fecha deben ser completados.';
                error_log($response['message']);
            } elseif($fecha_inicio > $fecha_fin){
                $response['message'] = "La fecha de fin debe ser mayor o igual a la fecha de inicio.";
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

                error_log("Todo en orden");

                if ($response['message'] === '') {
                    //url usada en el correo
                    $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/anticipos';

                    if ($this->anticipoModel->anticipoPendiente($id_usuario)) {
                    $response['message'] = 'El solicitante aún tiene pendiente un anticipo por rendir.';
                    error_log($response['message']);
                    } else {
                        error_log("El solicitante no tiene un anticipo pendiente por rendir");
                        // Verificar saldo disponible
                        $saldo_disponible = $this->anticipoModel->getSaldoDisponibleBySscc($codigo_sscc);
                        if ($saldo_disponible === null) {
                            $response['message'] = 'No se encontró un presupuesto activo para el sub-subcentro seleccionado.';
                            error_log($response['message']);
                        } elseif ($monto_total_solicitado > $saldo_disponible) {
                            $response['message'] = "No se registró el anticipo. El monto solicitado ($monto_total_solicitado) excede el saldo disponible ($saldo_disponible) para el sub-subcentro.";
                            error_log($response['message']);
                        } else {
                            $numero_anticipo = $this->anticipoModel->addAnticipo($id_usuario, $solicitante, $solicitante_nombres, $dni_solicitante, $departamento, $departamento_nombre, $codigo_sscc, $cargo, $nombre_proyecto, $fecha_solicitud, $fecha_inicio, $fecha_fin, $motivo_anticipo, $monto_total_solicitado, $id_cat_documento, $detalles_gastos, $detalles_viajes);
                            
                            date_default_timezone_set('America/Lima');
                            $now = new DateTime(); // fecha y hora actual en Lima
                            $dayOfWeek = $now->format('N'); // 2 = martes, 5 = viernes
                            $hour = (int)$now->format('H');

                            // Calcular la próxima fecha de atención si aplica
                            $proxima_fecha = null;
                            if ($dayOfWeek == 2 && $hour >= 12) {
                                // Martes después de mediodía → próximo viernes
                                $proxima_fecha = (clone $now)->modify('next friday');
                            } elseif ($dayOfWeek == 5 && $hour >= 12) {
                                // Viernes después de mediodía → próximo martes
                                $proxima_fecha = (clone $now)->modify('next tuesday');
                            }

                            if ($proxima_fecha) {
                                 $formatter = new IntlDateFormatter(
                                    'es_PE',
                                    IntlDateFormatter::FULL,  // ejemplo: martes, 2 de septiembre de 2025
                                    IntlDateFormatter::NONE,
                                    'America/Lima',
                                    IntlDateFormatter::GREGORIAN,
                                    "EEEE, d 'de' MMMM"
                                );
                                $fecha_formateada = $formatter->format($proxima_fecha);

                                $respuesta = "Su solicitud de anticipo ha sido registrada correctamente. "
                                . "Por motivos de horario, se programa la atención para el próximo "
                                . $fecha_formateada . ".";
                                //$respuesta = "Su anticipo estará siendo atendido en la próxima fecha de atención: $fecha_formateada";
                            } else {
                                /*$response['message'] = "Anticipo registrado con éxito";*/
                                $respuesta = "Anticipo registrado con éxito";
                            }


                            // Aquí se puede validar la fecha y hora actual (no considerar la fecha y hora del dispositivo America/ Lima) UCT-NOW(Datetime).
                            if ($numero_anticipo) {
                                $response['success'] = true;
                                $response['message'] = $respuesta;

                                $solicitante = $this->trabajadorModel->getTrabajadorByDni($dni_solicitante);

                                if ($solicitante && isset($solicitante['correo'])) {
                                    // formatear
                                    $fecha_correo = date("d-m-Y", strtotime($fecha_solicitud));
                                    $monto_correo = number_format($monto_total_solicitado, 2, ',', '.');

                                    $to = $solicitante['correo'];
                                    $subject = "SIAR - TECHING - Anticipo N° $numero_anticipo ha sido creado";

                                    $aprobadores = $this->trabajadorModel->getAprobadoresByDepartamento($departamento);

                                    $body = "
                                        <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                                        <h2>Notificación de Anticipo</h2>
                                        <p>Estimado/a, {$solicitante['nombres']} {$solicitante['apellidos']},</p>
                                        <p>Se ha creado un nuevo anticipo con los siguientes detalles:</p>
                                        <ul>
                                            <li><strong>N° Anticipo:</strong> $numero_anticipo</li>
                                            <li><strong>Motivo:</strong> $motivo_anticipo</li>
                                            <li><strong>DNI Solicitante:</strong> $dni_solicitante</li>
                                            <li><strong>Sub sub-centro de costo:</strong> $codigo_sscc</li>
                                            <li><strong>Nombre del Proyecto:</strong> $nombre_proyecto</li>
                                            <li><strong>Fecha:</strong> $fecha_correo</li>
                                            <li><strong>Monto:</strong> $monto_correo</li>
                                        </ul>
                                        <p>Recuerde que este anticipo deberá ser autorizado para que se pueda continuar con la atención de su solicitud..</p>
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
                'fecha_inicio' => $_POST['edit-fecha-ejecucion'],
                'fecha_fin' => $_POST['edit-fecha-finalizacion'],
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

                    if(!empty($viaje['transporte']) && is_array($viaje['transporte'])){
                        foreach ($viaje['transporte'] as $tIndex => $transporte) {
                            if ($transporte['valido'] === '1') {
                                if (empty($transporte['tipo_transporte']) || empty($transporte['ciudad_origen']) || empty($transporte['ciudad_destino']) || empty($transporte['fecha']) || empty($transporte['monto']) || empty($transporte['moneda'])) {
                                    echo json_encode(['error' => "Datos incompletos en detalles_viajes[$index][transporte][$tIndex]"]);
                                    return;
                                }
                            }
                        }
                    }
                    /*
                    foreach ($viaje['viaticos'] as $concepto => $viatico) {
                        if (!isset($viatico['dias']) || !isset($viatico['monto']) || $viatico['dias'] < 0 || $viatico['monto'] < 0) {
                            echo json_encode(['error' => "Datos incompletos o inválidos en detalles_viajes[$index][viaticos][$concepto]"]);
                            return;
                        }
                    }*/
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
                                <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
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

    public function getDocAutorizacion() {
        //$nombreSolicitante = trim($_POST['solicitante']) ?? '---';
        $datos = json_decode($_POST['datos'], true);

        $doc = new DocumentService();
        $doc->generarDesdePlantilla($datos);
    }

    public function guardar_adjunto() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo']) && isset($_POST['id_anticipo'])) {
            $id_anticipo = (int)$_POST['id_anticipo'];
            $archivo = $_FILES['archivo'];
            $nombre_original = $_POST['nombre_original'] ?? basename($archivo['name']);

            // Validar archivo
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            if (!in_array($archivo['type'], $allowedTypes)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Tipo de archivo no permitido.']);
                exit;
            }

            // Directorio de uploads
            $uploadDir = 'uploads/anticipos/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $nombreArchivo = uniqid() . '_' . basename($archivo['name']);
            $rutaArchivo = $uploadDir . $nombreArchivo;

            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
                // Verificar si ya existe un adjunto para este anticipo
                $query = "SELECT id FROM tb_anticipos_adjuntos WHERE id_anticipo = :id_anticipo AND estado = 1";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':id_anticipo' => $id_anticipo]);
                $existente = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existente) {
                    // Actualizar el existente (reemplazar)
                    $query = "UPDATE tb_anticipos_adjuntos SET nombre_archivo = :nombre, ruta_archivo = :ruta, tipo_archivo = :tipo, fecha_subida = NOW(), nombre_original = :nombre_original WHERE id = :id";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([
                        ':nombre' => $nombreArchivo,
                        ':ruta' => $rutaArchivo,
                        ':tipo' => $archivo['type'],
                        ':nombre_original' => $nombre_original,
                        ':id' => $existente['id']
                    ]);
                } else {
                    // Insertar nuevo
                    $query = "INSERT INTO tb_anticipos_adjuntos (id_anticipo, nombre_archivo, ruta_archivo, tipo_archivo, nombre_original) VALUES (:id_anticipo, :nombre, :ruta, :tipo, :nombre_original)";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([
                        ':id_anticipo' => $id_anticipo,
                        ':nombre' => $nombreArchivo,
                        ':ruta' => $rutaArchivo,
                        ':tipo' => $archivo['type'],
                        ':nombre_original' => $nombre_original
                    ]);
                }

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'ruta' => $rutaArchivo]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Error al mover el archivo.']);
                exit;
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Solicitud inválida.']);
        exit;
    }

    public function obtener_adjunto() {
        if (isset($_GET['id_anticipo'])) {
            $id_anticipo = (int)$_GET['id_anticipo'];
            $query = "SELECT nombre_archivo, ruta_archivo, nombre_original FROM tb_anticipos_adjuntos WHERE id_anticipo = :id_anticipo AND estado = 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id_anticipo' => $id_anticipo]);
            $adjunto = $stmt->fetch(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode($adjunto ? $adjunto : []);
            exit;
        }
        header('Content-Type: application/json');
        echo json_encode([]);
        exit;
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
                    /*$dnisRol5 = $this->trabajadorModel->getDnisByRol5();
                    $rol5Correos = [];
                    if (!empty($dnisRol5)) {
                        // Buscar correos en tb_trabajadores (base externa) para los DNI con rol 5
                        foreach ($dnisRol5 as $dni) {
                            $trabajador = $this->trabajadorModel->getTrabajadorByDni($dni);
                            if ($trabajador && isset($trabajador['correo'])) {
                                $rol5Correos[] = $trabajador['correo'];
                            }
                        }
                    }*/

                    $body = "
                        <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
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
                    /*if (!empty($rol5Correos)) {
                        $bodyTesoreria = "
                            <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
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
                    }*/
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

    // Funcionalidad para autorizar un anticipo como gerencia
    public function autorizacionGerencia(){
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
            if (!in_array($latestEstado, ['Autorizado'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'El anticipo no puede ser marcado como autorizado por gerencia. Solo se pueden autorizar como gerencia anticipos en estado "Autorizado".']);
                exit;
            }

            if ($this->anticipoModel->updateAnticipoEstado($id, 'Autorizado por Gerencia', $_SESSION['id'], 'Autorizado por Gerencia')) {
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
                    $subject = "SIAR - TECHING - Anticipo N° $id ha sido autorizado por gerencia";

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
                        <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                        <h2>Notificación de Anticipo</h2>
                        <p>Se realizó la autorización de gerencia correctamente. Información del anticipo:</p>
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
                            <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                            <h2>Notificación de Anticipo</h2>
                            <p>El usuario aprobador realizó la autorización de gerencia correctamente. Información del anticipo:</p>
                            <ul>
                                <li><strong>N° de Anticipo:</strong> $id</li>
                                <li><strong>Motivo:</strong> $motivoAnticipo</li>
                                <li><strong>DNI Solicitante:</strong> $dniSolicitante</li>
                                <li><strong>Nombre Solicitante: $solicitanteNombre</strong></li>
                                <li><strong>Sub sub-centro de costo:</strong> $sscc</li>
                                <li><strong>Nombre del Proyecto:</strong> $nombreProyecto</li>
                                <li><strong>Monto:</strong> $montoTotal</li>
                            </ul>
                            <p>Deberá revisar en la plataforma SIAR el anticipo respectivo para poder realizar la autorización total o marcarlo como observado en caso exista un dato incorrecto.</p>
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
            if (!in_array($latestEstado, ['Autorizado por Gerencia'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'El anticipo no puede ser autorizado. Solo se pueden autorizar totalmente anticipos en estado "Autorizado por Gerencia".']);
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
                        <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                        <h2>Notificación de Anticipo</h2>
                        <p>Se realizó autorización del anticipo correctamente, por el área de tesorería. Información del anticipo:</p>
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
                            <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                            <h2>Notificación de Anticipo</h2>
                            <p>Se realizó la autorización correctamente, por parte de teserería. Información del anticipo:</p>
                            <ul>
                                <li><strong>N° de Anticipo:</strong> $id</li>
                                <li><strong>Motivo:</strong> $motivoAnticipo</li>
                                <li><strong>DNI Solicitante:</strong> $dniSolicitante</li>
                                <li><strong>Nombre Solicitante: $solicitanteNombre</strong></li>
                                <li><strong>Sub sub-centro de costo:</strong> $sscc</li>
                                <li><strong>Nombre del Proyecto:</strong> $nombreProyecto</li>
                                <li><strong>Monto:</strong> $montoTotal</li>
                            </ul>
                            <p>Deberá revisar en la plataforma SIAR el anticipo respectivo para poder continuar con el proceso de anticipo.</p>
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
            if (!in_array($latestEstado, ['Autorizado por Gerencia'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'El anticipo no pudo ser marcado como observado. Solo se pueden observar anticipos en estado "Autorizado por Gerencia".']);
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
                        <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
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
            //error_log('No tiene permisos para realizar este tipo de actividad');
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
            $fechaFin = $_POST['fechaFin'];
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

            if ($this->anticipoModel->abonarAnticipo($id, $_SESSION['id'], $comentario, $fechaFin)) {

                $correo_tesorero = $_SESSION['trabajador']['correo'];

                //url usada en el correo
                $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/anticipos';

                $solicitante = $this->trabajadorModel->getTrabajadorByDni($dniSolicitante);
                $correo_solicitante = $solicitante['correo'] ?? 'null';

                $correos_cc = [$correo_solicitante] ? [$correo_solicitante] : [];

                if ($correo_solicitante) {

                    $to = $correo_tesorero;
                    $subject = "SIAR - TECHING - Anticipo N° $id abonado";

                    $body = "
                        <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
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
                        error_log("Anticipo abonado y notificación enviada");
                    } else {
                        error_log("No se pudo enviar la notificación");
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

    public function getComprasMenores() {
        header('Content-Type: application/json');

        if (!isset($_GET['anticipo_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Anticipo ID is required']);
            exit;
        }

        $anticipoId = $_GET['anticipo_id'];
        $comprasMenores = $this->anticipoModel->getComprasMenoresByAnticipoId($anticipoId);
        echo json_encode(['success' => true, 'data' => $comprasMenores]);
        exit;
    }

    public function getViaticos() {
        header('Content-Type: application/json');
        if (!isset($_GET['anticipo_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Anticipo ID is required']);
            exit;
        }
        $anticipoId = $_GET['anticipo_id'];
        $viaticos = $this->anticipoModel->getViaticosByAnticipoId($anticipoId);
        echo json_encode(['success' => true, 'data' => $viaticos]);
        exit;
    }

    public function getTransporteProvincial() {
        header('Content-Type: application/json');
        if (!isset($_GET['anticipo_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Anticipo ID is required']);
            exit;
        }
        $anticipoId = $_GET['anticipo_id'];
        $transporte = $this->anticipoModel->getTransporteProvincialByAnticipoId($anticipoId);
        echo json_encode(['success' => true, 'data' => $transporte]);
        exit;
    }
}
?>