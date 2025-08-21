<?php
require_once 'src/config/Database.php';
require_once 'src/models/TrabajadorModel.php';
require_once 'src/models/RendicionesModel.php';
require_once 'src/config/EmailConfig.php';

class RendicionesController {
    private $db;
    private $rendicionesModel;
    private $emailConfig;
    private $logoBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAH4AAAApCAYAAADzqJ3HAAAA8HpUWHRSYXcgcHJvZmlsZSB0eXBlIGV4aWYAAHjajVFZbsUgDPznFD2CNxYfh2xSb/CO/ybBvDSVKtUSxh4bMwxpf30f6es0aZIs11a8FIKZm0tH0GjYcnkmu/xldY8aP/EkHgUBpNh1pC6B78ARc+Qel/Dsn4NmwB1Rvgu9B7488SUGSvs9KBgoj5tpiwMxSCUY2cjXYFS81cfTtpWe1u5lWqXkwtXgTajW4oibkFXouZ1Ej3WIk/IQ9APMfLYKOMmurASvWgZLPZdpx57hUU1oZIAn5PCicglP+EpQAPP4jKPTR8yf2twa/WH/eVZ6A6DCdu3Vw4HaAAABhGlDQ1BJQ0MgcHJvZmlsZQAAeJx9kT1Iw0AcxV/TlmqpdLCDiEOG6mQXFXEsVSyChdJWaNXB5NIvaNKSpLg4Cq4FBz8Wqw4uzro6uAqC4AeIs4OToouU+L+k0CLGg+N+vLv3uHsHCO0aUw1fHFA1U88kE2K+sCoGXuFHEGH4MCgxo5HKLubgOr7u4eHrXYxnuZ/7cwwpRYMBHpE4zhq6SbxBPLtpNjjvE0dYRVKIz4kndbog8SPXZYffOJdtFnhmRM9l5okjxGK5j+U+ZhVdJZ4hjiqqRvlC3mGF8xZntdZk3XvyF4aK2kqW6zTHkMQSUkhDhIwmqqjBRIxWjRQDGdpPuPhHbX+aXDK5qmDkWEAdKiTbD/4Hv7s1StNTTlIoAfhfLOtjHAjsAp2WZX0fW1bnBPA+A1daz19vA3OfpLd6WvQICG8DF9c9Td4DLneAkaeGpEu25KUplErA+xl9UwEYvgWCa05v3X2cPgA56mr5Bjg4BCbKlL3u8u6B/t7+PdPt7wcpY3KJn+OU0AAADXZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IlhNUCBDb3JlIDQuNC4wLUV4aXYyIj4KIDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+CiAgPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIKICAgIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIgogICAgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIKICAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIKICAgIHhtbG5zOkdJTVA9Imh0dHA6Ly93d3cuZ2ltcC5vcmcveG1wLyIKICAgIHhtbG5zOnRpZmY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vdGlmZi8xLjAvIgogICAgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIgogICB4bXBNTTpEb2N1bWVudElEPSJnaW1wOmRvY2lkOmdpbXA6Y2VkODNkNjUtNGY4ZC00MjFmLTkyOTItNDk2N2M2Nzc3YWNiIgogICB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjkyYTE3YTMzLWY3NGQtNDliMy1hZDQ0LWY4ZWZhMjhkYzQ0YyIKICAgeG1wTU06T3JpZ2luYWxEb2N1bWVudElEPSJ4bXAuZGlkOjA1MjllZWRmLTFiZGUtNGM5OS1iOGUxLTZiOTk2NjIyMzNjZCIKICAgZGM6Rm9ybWF0PSJpbWFnZS9wbmciCiAgIEdJTVA6QVBJPSIyLjAiCiAgIEdJTVA6UGxhdGZvcm09IldpbmRvd3MiCiAgIEdJTVA6VGltZVN0YW1wPSIxNzE1MzYwOTY0MDAwNDAwIgogICBHSU1QOlZlcnNpb249IjIuMTAuMzYiCiAgIHRpZmY6T3JpZW50YXRpb249IjEiCiAgIHhtcDpDcmVhdG9yVG9vbD0iR0lNUCAyLjEwIgogICB4bXA6TWV0YWRhdGFEYXRlPSIyMDI0OjA1OjEwVDEyOjA5OjIyLTA1OjAwIgogICB4bXA6TW9kaWZ5RGF0ZT0iMjAyNDowNToxMFQxMjowOToyMi0wNTowMCI+CiAgIDx4bXBNTTpIaXN0b3J5PgogICAgPHJkZjpTZXE+CiAgICAgPHJkZjpsaQogICAgICBzdEV2dDphY3Rpb249InNhdmVkIgogICAgICBzdEV2dDpjaGFuZ2VkPSIvIgogICAgICBzdEV2dDppbnN0YW5jZUlEPSJ4bXAuaWlkOmZkMGQ5ODdjLTY4MDgtNGViNi1hYjA5LTRlOTJkMTI0NDM3ZiIKICAgICAgc3RFdnQ6c29mdHdhcmVBZ2VudD0iR2ltcCAyLjEwIChXaW5kb3dzKSIKICAgICAgc3RFdnQ6d2hlbj0iMjAyNC0wNS0xMFQxMjowOToyMyIvPgogICAgPC9yZGY6U2VxPgogICA8L3htcE1NOkhpc3Rvcnk+CiAgPC9yZGY6RGVzY3JpcHRpb24+CiA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIAogICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAKICAgICAgICAgICAgICAgICAgICAgICAgICAgCjw/eHBhY2tldCBlbmQ9InciPz48TZWsAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAC4jAAAuIwF4pT92AAAAB3RJTUUH6AUKEQkX3murkgAAABl0RVh0Q29tbWVudABDcmVhdGVkIHdpdGggR0lNUFeBDhcAAA+XSURBVHja7Zt5dFX1tcc/+9ybBDIwCQgBhUVQZlEeg7W2opQCySUDiuDAJPbhwLJqFZ+KONBqZ8Q+7bMViNBaFc14AxQVHKiKICQMKiBjGASEEMhA7vDb7497Qi7h3swVoey1zlrJub9zfsN3D9+9f78jXJD6SdqKZlA+AFUXMACIBnaDuPFLHrmJJefCNOQCkrXL/I04LYNTLcQCUDEZb86TnM3tf6rKA8BAIAZYjSGVHNfBC8Cfg/J/62KlmbOkJRrZCfFcDtIDtJtCtL1gRSAbQD9dsfzlPQtXdboBeAroDfIiwmNkJpV9n+fo/I9ENnXJRai5HEsSUO0AGIRvMGzD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';

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->rendicionesModel = new RendicionesModel();
        $this->trabajadorModel = new TrabajadorModel();
        $this->emailConfig = new EmailConfig();
    }

    public function index(){
        if (!isset($_SESSION['id'])) {
            header('Location: iniciar_sesion');
            exit;
        }
        $rendiciones_data = $this->rendicionesModel->getRendicionesByRole($_SESSION['id'], $_SESSION['rol']);
        require_once 'src/views/rendiciones.php';
    }

    public function getRendicionDetails(){
        header('Content-Type: application/json');
        if (isset($_GET['id_rendicion'])){
            $id_rendicion = $_GET['id_rendicion'];
            $rendicion = $this->rendicionesModel->getRendicionById($id_rendicion);

            if($rendicion){
                echo json_encode($rendicion);
            }else{
                echo json_encode((['error' => 'Rendición noencontrada']));
            }
        }else{
            echo json_encode((['error'=> 'No se proporcionó el id de la rendición']));
        }
        exit;
    }

    public function getDetallesComprasMenores() {
        header('Content-Type: application/json');
        if (isset($_GET['id_anticipo'])) {
            $id_anticipo = $_GET['id_anticipo'];
            $detalles = $this->rendicionesModel->getDetallesComprasMenoresByAnticipo($id_anticipo);
            echo json_encode($detalles);
        } else {
            echo json_encode(['error' => 'No se proporcionó el id del anticipo']);
        }
        exit;
    }

    // Nuevas rutas para viáticos
    public function getDetallesViajes() {
        header('Content-Type: application/json');
        if (isset($_GET['id_anticipo'])) {
            $id_anticipo = $_GET['id_anticipo'];
            $detalles = $this->rendicionesModel->getDetallesViajesByAnticipo($id_anticipo);
            echo json_encode($detalles);
        } else {
            echo json_encode(['error' => 'No se proporcionó el id del anticipo']);
        }
        exit;
    }

    // Nuevas rutas para transportes
    public function getDetallesTransportes() {
        header('Content-Type: application/json');
        if (isset($_GET['id_anticipo'])) {
            $id_anticipo = $_GET['id_anticipo'];
            $detalles = $this->rendicionesModel->getDetallesTransportesByAnticipo($id_anticipo);
            echo json_encode($detalles);
        } else {
            echo json_encode(['error' => 'No se proporcionó el id del anticipo']);
        }
        exit;
    }

    public function getMontoSolicitadoByAnticipo() {
        if (isset($_GET['id_anticipo'])) {

            $monto = $this->rendicionesModel->getMontoSolicitadoByAnticipo($_GET['id_anticipo']);
            echo json_encode($monto);
        } else {
            echo json_encode(0.00);
        }
        exit;
    }

    public function getMontoTotalRendidoByRendicion() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id_rendicion = $_GET['id_rendicion'] ?? '';

            if (!$id_rendicion) {
                echo json_encode(['success' => false, 'error' => 'Parámetro id_rendicion es requerido']);
                return;
            }

            try {
                $query = "SELECT COALESCE(SUM(c.importe_total), 0) as total_rendido FROM (
                    SELECT importe_total FROM tb_comprobantes_compras WHERE id_rendicion = :id_rendicion1
                    UNION ALL
                    SELECT importe_total FROM tb_comprobantes_viaticos WHERE id_rendicion = :id_rendicion2
                    UNION ALL
                    SELECT importe_total FROM tb_comprobantes_transportes WHERE id_rendicion = :id_rendicion3
                ) c";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':id_rendicion1' => $id_rendicion, ':id_rendicion2' => $id_rendicion, ':id_rendicion3' => $id_rendicion]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'monto_total' => floatval($result['total_rendido'])]);
            } catch (PDOException $e) {
                error_log('Error al obtener monto total rendido: ' . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        }
    }

    public function getMontoTotalRendidoByDetalle() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id_rendicion = $_GET['id_rendicion'] ?? '';
            $id_detalle = $_GET['id_detalle'] ?? '';
            $tipo = strtolower($_GET['tipo'] ?? '');

            if (!$id_rendicion || !$id_detalle || !$tipo) {
                echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
                return;
            }

            // Mapear tipos válidos a nombres de tablas
            $tipoTablaMap = [
                'compra' => 'tb_comprobantes_compras',
                'viatico' => 'tb_comprobantes_viaticos',
                'transporte' => 'tb_comprobantes_transportes'
            ];

            if (!isset($tipoTablaMap[$tipo])) {
                echo json_encode(['success' => false, 'error' => 'Tipo de comprobante no válido']);
                return;
            }

            $tabla = $tipoTablaMap[$tipo];

            try {
                $query = "SELECT COALESCE(SUM(importe_total), 0) as monto_total FROM $tabla WHERE id_rendicion = :id_rendicion AND id_detalle = :id_detalle";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':id_rendicion' => $id_rendicion, ':id_detalle' => $id_detalle]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'monto_total' => floatval($result['monto_total'])]);
            } catch (PDOException $e) {
                error_log('Error al obtener monto rendido por detalle: ' . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function getLatestEstadoRendicion() {
        if (isset($_GET['id_rendicion'])) {
            $estado = $this->rendicionesModel->getLatestEstadoRendicion($_GET['id_rendicion']);
            header('Content-Type: application/json');
            echo json_encode($estado);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['estado' => 'Nuevo']);
        }
        exit;
    }

    public function aprobarRendicion() {
        $id_rendicion = $_POST['id_rendicion'];
        $id_aprobador = $_POST['id_usuario'];
        $dni_responsable = $_POST['dni_responsable'];
        $id_anticipo = $_POST['id_anticipo'];
        $motivo_anticipo = $_POST['motivo_anticipo'];
        $nombre_responsable = $_POST['nombre_responsable'];
        $codigo_sscc = $_POST['codigo_sscc'];
        $monto_solicitado = $_POST['monto_solicitado'];
        $monto_rendido = $_POST['monto_rendido_actualmente'];

        //correos de envío
        $correo_aprobador = $_SESSION['trabajador']['correo'];
        $responsable = $this->trabajadorModel->getTrabajadorByDni($dni_responsable);
        $correo_responsable = $responsable['correo'];
        $correos_cc = [$correo_responsable];

        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
            error_log('No tiene permisos para realizar este tipo de actividad');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tiene permisos para realizar este tipo de actividad']);
            exit;
        }

        if (isset($_POST['id_rendicion']) && isset($_POST['id_usuario'])) {
            $model = new RendicionesModel();
            $success = $model->aprobarRendicion($id_rendicion, $id_aprobador);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'error' => $success ? '' : 'Error al realizar la autorización']);

            $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/rendiciones';
            if($correo_aprobador){
                $to = $correo_aprobador;
                $subject = "SIAR - TECHING - Rendición N° $id_rendicion ha sido autorizado";

                // obteniendo información de los contadores
                $dnisRol4 = $this->trabajadorModel->getDnisByRol4();
                $rol4Correos = [];
                if (!empty($dnisRol4)) {
                    // Buscar correos en tb_trabajadores (base externa) para los DNI con rol 5
                    foreach ($dnisRol4 as $dni) {
                        $trabajador = $this->trabajadorModel->getTrabajadorByDni($dni);
                        if ($trabajador && isset($trabajador['correo'])) {
                            $rol4Correos[] = $trabajador['correo'];
                        }
                    }
                }

                $body = "
                    <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                    <h2>Notificación de Rendición</h2>
                    <p>Rendición autorizada. Información:</p>
                    <ul>
                        <li><strong>N° de Rendición:</strong> $id_rendicion</li>
                        <li><strong>N° de Anticipo relacionado:</strong> $id_anticipo</li>
                        <li><strong>DNI Responsable:</strong> $dni_responsable</li>
                        <li><strong>Nombre Responsable:</strong> $nombre_responsable</li>
                        <li><strong>Motivo de la solicitud de anticipo:</strong> $motivo_anticipo</li>
                        <li><strong>Sub sub-centro de costo:</strong> $codigo_sscc</li>
                        <li><strong>Monto Solicitado:</strong> $monto_solicitado</li>
                        <li><strong>Monto Rendido:</strong> $monto_rendido</li>
                    </ul>
                    <p>Deberá revisar que toda la información ingresada sea correcta.</p>
                    <p>Recuerde que esta rendición deberá ser autorizada posteriormente por el área de <b>contabilidad</b> para que así se finalice con el proceso.</p>
                    <hr>
                    <br>
                    <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles de la rendición.</p>
                    <a href='{$url_plataforma}'>SIAR - TECHING</a>
                ";

                if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $correos_cc)) {
                    error_log("Rendición autorizada y notificación enviada");
                } else {
                    error_log("No se pudo enviar la notificación");
                }

                // Enviar correo a usuarios con rol 5
                if (!empty($rol4Correos)) {
                    $bodyContador = "
                        <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                        <h2>Notificación de Rendición</h2>
                        <p>El usuario aprobador realizó la primera autorización correctamente. Información del anticipo:</p>
                        <ul>
                            <li><strong>N° de Rendición:</strong> $id_rendicion</li>
                            <li><strong>N° de Anticipo relacionado:</strong> $id_anticipo</li>
                            <li><strong>DNI Responsable:</strong> $dni_responsable</li>
                            <li><strong>Nombre Responsable:</strong> $nombre_responsable</li>
                            <li><strong>Motivo de la solicitud de anticipo:</strong> $motivo_anticipo</li>
                            <li><strong>Sub sub-centro de costo:</strong> $codigo_sscc</li>
                            <li><strong>Monto Solicitado:</strong> $monto_solicitado</li>
                            <li><strong>Monto Rendido:</strong> $monto_rendido</li>
                        </ul>
                        <p>Deberá revisar en la plataforma SIAR la rendición respectiva para poder finalizarla o marcarla como observado en caso exista un dato incorrecto.</p>
                        <hr>
                        <br>
                        <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles de la rendición.</p>
                        <a href='{$url_plataforma}'>SIAR - TECHING</a>
                    ";
                    foreach ($rol4Correos as $rol4Correo) {
                        if ($this->emailConfig->sendSiarNotification($rol4Correo, $subject, $bodyContador, [])) {
                            error_log("Notificación enviada al usuario con rol 5: $rol4Correo");
                        } else {
                            error_log("No se pudo enviar la notificación al usuario con rol 5: $rol4Correo");
                        }
                    }
                }
            }

        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Datos incompletos, no se pudo realizar la autorización']);
        }
        exit;
    }

    public function observarRendicion() {
        $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/rendiciones';

        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 4) {
            error_log('No tiene permisos para realizar este tipo de actividad');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tiene permisos para realizar este tipo de actividad']);
            exit;
        }

        if (isset($_POST['id_rendicion']) && isset($_POST['id_usuario'])) {
            $id_rendicion = $_POST['id_rendicion'];
            $id_usuario = $_POST['id_usuario'];
            $comentario = $_POST['comentario'];
            $dni_responsable = $_POST['dni_responsable'];
            $id_anticipo = $_POST['id_anticipo'];
            $motivo_anticipo = $_POST['motivo_anticipo'];
            $nombre_responsable = $_POST['nombre_responsable'];
            $codigo_sscc = $_POST['codigo_sscc'];
            $monto_solicitado = $_POST['monto_solicitado'];
            $monto_rendido = $_POST['monto_rendido_actual'];

            $model = new RendicionesModel();
            $result = $model->observarRendicion($id_rendicion, $id_usuario, $comentario);

            // Correo del contador que está marcando la rendición como observada
            $correo_contador = $_SESSION['trabajador']['correo'];

            // Obtener el correo del autorizador
            $idAutorizador = $this->rendicionesModel->getLastAuthorizerId($id_rendicion);
            $dniAutorizador = $this->trabajadorModel->getDniById($idAutorizador);
            $autorizador = $idAutorizador ? $this->trabajadorModel->getTrabajadorByDni($dniAutorizador) : null;
            $correo_autorizador = $autorizador && isset($autorizador['correo']) ? $autorizador['correo'] : null;
            error_log("Correo del autorizador". $correo_autorizador);

            // Obteniendo el correo del responsable
            $responsable = $this->trabajadorModel->getTrabajadorByDni($dni_responsable);
            $correo_responsable = $responsable['correo'];

            $correos_cc = [$correo_responsable];
            if ($correo_autorizador) {
                $correos_cc[] = $correo_autorizador; // Añadir el correo del autorizador a CC
            }

            if ($correo_responsable) {

                $to = $correo_contador;
                $subject = "SIAR - TECHING - Rendición N° $id_rendicion ha sido marcado como observado";

                $body = "
                    <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                    <h2>Notificación de Rendición</h2>
                    <p>Rendición marcada como observada. Información:</p>
                    <ul>
                        <li><strong>N° de Rendición:</strong> $id_rendicion</li>
                        <li><strong>N° de Anticipo relacionado:</strong> $id_anticipo</li>
                        <li><strong>DNI Responsable:</strong> $dni_responsable</li>
                        <li><strong>Nombre Responsable:</strong> $nombre_responsable</li>
                        <li><strong>Motivo de la solicitud de anticipo:</strong> $motivo_anticipo</li>
                        <li><strong>Sub sub-centro de costo:</strong> $codigo_sscc</li>
                        <li><strong>Monto Solicitado:</strong> $monto_solicitado</li>
                        <li><strong>Monto Rendido:</strong> $monto_rendido</li>
                        <p><strong>Observación:</strong> $comentario</p>
                    </ul>
                    <p>Deberá de modificar los datos de la rendición considerando el comentario de observación.</p>
                    <p>Recuerde que tras haber culminado con la actualización de datos, deberá volver a autorizar la rendición para que se pueda continuar con la atención.</p>
                    <hr>
                    <br>
                    <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles de la rendición.</p>
                    <a href='{$url_plataforma}'>SIAR - TECHING</a>
                ";

                error_log("Correo del responsable" .$correo_responsable);

                if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $correos_cc)) {
                    error_log("Anticipo marcado como observado y con notificación enviada");
                } else {
                    error_log("No se pudo enviar la notificación");
                }

            } else {
                error_log("No se envió el correo, no se encontró el correo del solicitante");
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
        }
    }

    public function corregirRendicion(){
        $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/rendiciones';

         if (isset($_POST['id_rendicion']) && isset($_POST['id_usuario'])) {
            $id_rendicion = $_POST['id_rendicion'];
            $id_usuario = $_POST['id_usuario'];
            $comentario = $_POST['comentario'];
            $dni_responsable = $_POST['dni_responsable'];
            $id_anticipo = $_POST['id_anticipo'];
            $motivo_anticipo = $_POST['motivo_anticipo'];
            $nombre_responsable = $_POST['nombre_responsable'];
            $codigo_sscc = $_POST['codigo_sscc'];
            $monto_solicitado = $_POST['monto_solicitado'];
            $monto_rendido = $_POST['monto_rendido_actual'];

            $model = new RendicionesModel();
            $result = $model->corregirRendicion($id_rendicion, $id_usuario, $comentario);

            // Obtener el correo del autorizador
            $idAutorizador = $this->rendicionesModel->getLastAuthorizerId($id_rendicion);
            $dniAutorizador = $this->trabajadorModel->getDniById($idAutorizador);
            $autorizador = $idAutorizador ? $this->trabajadorModel->getTrabajadorByDni($dniAutorizador) : null;
            $correo_autorizador = $autorizador && isset($autorizador['correo']) ? $autorizador['correo'] : null;
            error_log("Correo del autorizador". $correo_autorizador);

            // Obteniendo el correo del responsable
            $responsable = $this->trabajadorModel->getTrabajadorByDni($dni_responsable);
            $correo_responsable = $responsable['correo'];

            $correos_cc = [];
            if ($correo_autorizador) {
                $correos_cc[] = $correo_autorizador; // Añadir el correo del autorizador a CC
            }

            if ($correo_responsable) {

                $to = $correo_responsable;
                $subject = "SIAR - TECHING - Rendición N° $id_rendicion ha sido actualizada";

                $body = "
                    <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                    <h2>Notificación de Rendición</h2>
                    <p>La rendición ha sido actualizada por el solicitante tras haber recibido una observación. Información:</p>
                    <ul>
                        <li><strong>N° de Rendición:</strong> $id_rendicion</li>
                        <li><strong>N° de Anticipo relacionado:</strong> $id_anticipo</li>
                        <li><strong>DNI Responsable:</strong> $dni_responsable</li>
                        <li><strong>Nombre Responsable:</strong> $nombre_responsable</li>
                        <li><strong>Motivo de la solicitud de anticipo:</strong> $motivo_anticipo</li>
                        <li><strong>Sub sub-centro de costo:</strong> $codigo_sscc</li>
                        <li><strong>Monto Solicitado:</strong> $monto_solicitado</li>
                        <li><strong>Monto Rendido:</strong> $monto_rendido</li>
                        <p><strong>Observación:</strong> $comentario</p>
                    </ul>
                    <p>Deberá de buscar la rendición correspondiente y marcarla como autorizada.</p>
                    <p>Recuerde que tras haber culminado con esta autorización, personal del área de contabilidad, procederá a hacer la revisión para proceder con el proceso correspondiente.</p>
                    <hr>
                    <br>
                    <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles de la rendición.</p>
                    <a href='{$url_plataforma}'>SIAR - TECHING</a>
                ";

                error_log("Correo del responsable" .$correo_responsable);

                if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $correos_cc)) {
                    error_log("Anticipo marcado como nuevo y con notificación enviada");
                } else {
                    error_log("No se pudo enviar la notificación");
                }

            } else {
                error_log("No se envió el correo, no se encontró el correo del solicitante");
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
        }
    }

    public function completarRendicion(){
        $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/rendiciones';

         if (isset($_POST['id_rendicion']) && isset($_POST['id_usuario'])) {
            $id_rendicion = $_POST['id_rendicion'];
            $id_usuario = $_POST['id_usuario'];
            $comentario = $_POST['comentario'];
            $dni_responsable = $_POST['dni_responsable'];
            $id_anticipo = $_POST['id_anticipo'];
            $motivo_anticipo = $_POST['motivo_anticipo'];
            $nombre_responsable = $_POST['nombre_responsable'];
            $codigo_sscc = $_POST['codigo_sscc'];
            $monto_solicitado = $_POST['monto_solicitado'];
            $monto_rendido = $_POST['monto_rendido_actual'];

            $model = new RendicionesModel();
            $result = $model->completarRendicion($id_rendicion, $id_usuario, $comentario);

            // Obtener el correo del autorizador
            $idAutorizador = $this->rendicionesModel->getLastAuthorizerId($id_rendicion);
            $dniAutorizador = $this->trabajadorModel->getDniById($idAutorizador);
            $autorizador = $idAutorizador ? $this->trabajadorModel->getTrabajadorByDni($dniAutorizador) : null;
            $correo_autorizador = $autorizador && isset($autorizador['correo']) ? $autorizador['correo'] : null;
            error_log("Correo del autorizador". $correo_autorizador);

            // Obteniendo el correo del responsable
            $responsable = $this->trabajadorModel->getTrabajadorByDni($dni_responsable);
            $correo_responsable = $responsable['correo'];

            $correos_cc = [];
            if ($correo_autorizador) {
                $correos_cc[] = $correo_autorizador; // Añadir el correo del autorizador a CC
            }

            if ($correo_responsable) {

                $to = $correo_responsable;
                $subject = "SIAR - TECHING - Rendición N° $id_rendicion ha sido completada";

                $body = "
                    <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                    <h2>Notificación de Rendición</h2>
                    <p>La rendición ha sido marcada como completada por el solicitante. Información:</p>
                    <ul>
                        <li><strong>N° de Rendición:</strong> $id_rendicion</li>
                        <li><strong>N° de Anticipo relacionado:</strong> $id_anticipo</li>
                        <li><strong>DNI Responsable:</strong> $dni_responsable</li>
                        <li><strong>Nombre Responsable:</strong> $nombre_responsable</li>
                        <li><strong>Motivo de la solicitud de anticipo:</strong> $motivo_anticipo</li>
                        <li><strong>Sub sub-centro de costo:</strong> $codigo_sscc</li>
                        <li><strong>Monto Solicitado:</strong> $monto_solicitado</li>
                        <li><strong>Monto Rendido:</strong> $monto_rendido</li>
                    </ul>
                    <p>Deberá de buscar la rendición correspondiente y marcarla como autorizada.</p>
                    <p>Recuerde que tras haber culminado con esta autorización, personal del área de contabilidad, deberá realizar la revisión correspondiente para poder continuar con el proceso.</p>
                    <hr>
                    <br>
                    <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles de la rendición.</p>
                    <a href='{$url_plataforma}'>SIAR - TECHING</a>
                ";

                error_log("Correo del responsable" .$correo_responsable);

                if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $correos_cc)) {
                    error_log("Anticipo marcado como completado y con notificación enviada");
                } else {
                    error_log("No se pudo enviar la notificación");
                }

            } else {
                error_log("No se envió el correo, no se encontró el correo del solicitante");
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
        }
    }
    
    public function cerrarRendicion() {
        $url_plataforma = 'http://192.168.1.193/proy_anticipos_rendiciones/rendiciones';

        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 4) {
            error_log('No tiene permisos para realizar este tipo de actividad');
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No tiene permisos para realizar este tipo de actividad']);
            exit;
        }

        if (isset($_POST['id_rendicion']) && isset($_POST['id_usuario'])) {

            $id_rendicion = $_POST['id_rendicion'];
            $id_usuario = $_POST['id_usuario'];
            $comentario = $_POST['comentario'] ?? 'Rendición finalizada';
            $id_anticipo = $_POST['id_anticipo'];
            $dni_responsable = $_POST['dni_responsable'];
            $motivo_anticipo = $_POST['motivo_anticipo'];
            $nombre_responsable = $_POST['nombre_responsable'];
            $codigo_sscc = $_POST['codigo_sscc'];
            $monto_solicitado = $_POST['monto_solicitado'];
            $monto_rendido = $_POST['monto_rendido_actual'];

            $model = new RendicionesModel();
            $result = $model->cerrarRendicion($id_rendicion, $id_usuario, $comentario, $id_anticipo);

            // Correo del contador que está marcando la rendición como observada
            $correo_contador = $_SESSION['trabajador']['correo'];

            // Obteniendo el correo del responsable
            $responsable = $this->trabajadorModel->getTrabajadorByDni($dni_responsable);
            $correo_responsable = $responsable['correo'];

            $correos_cc = [$correo_responsable];

            if($correo_responsable){
                $to = $correo_contador;
                $subject = "SIAR - TECHING - Rendición N° $id_rendicion ha sido marcado como rendido";

                $body = "
                    <p><img alt='Logo SIAR' src='{$this->logoBase64}' width='140' /></p>
                    <h2>Notificación de Rendición</h2>
                    <p>Rendición finalizada. Información:</p>
                    <ul>
                        <li><strong>N° de Rendición:</strong> $id_rendicion</li>
                        <li><strong>N° de Anticipo relacionado:</strong> $id_anticipo</li>
                        <li><strong>DNI Responsable:</strong> $dni_responsable</li>
                        <li><strong>Nombre Responsable:</strong> $nombre_responsable</li>
                        <li><strong>Motivo de la solicitud de anticipo:</strong> $motivo_anticipo</li>
                        <li><strong>Sub sub-centro de costo:</strong> $codigo_sscc</li>
                        <li><strong>Monto Solicitado:</strong> $monto_solicitado</li>
                        <li><strong>Monto Rendido:</strong> $monto_rendido</li>
                        <p><strong>Proceso:</strong> $comentario</p>
                    </ul>
                    <p>Se ha finalizado con el proceso completo de rendición, ya puede solicitar un nuevo anticipo.</p>
                    <p>Se agradece su compromiso en completar la información solicitada.</p>
                    <hr>
                    <br>
                    <p>No es necesario responder a este mensaje. Deberá ingresar a la plataforma SIAR para obtener más detalles de la rendición.</p>
                    <a href='{$url_plataforma}'>SIAR - TECHING</a>
                ";

                error_log("Correo del responsable" .$correo_responsable);

                if ($this->emailConfig->sendSiarNotification($to, $subject, $body, $correos_cc)) {
                    error_log("Anticipo marcado como rendido y con notificación enviada");
                } else {
                    error_log("No se pudo enviar la notificación");
                }
            } else {
                error_log("No se envió el correo, no se encontró el correo del solicitante");
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
        }
    }

    /* Aquí inicia la nueva integración para poder registrar los comprobantes de rendiciones */
    public function getComprobantesByDetalle() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id_rendicion = isset($_GET['id_rendicion']) ? intval($_GET['id_rendicion']) : 0;
            $id_detalle = isset($_GET['id_detalle']) ? intval($_GET['id_detalle']) : 0;
            $tipo = isset($_GET['tipo']) ? strtolower($_GET['tipo']) : '';

            if ($id_rendicion <= 0 || $id_detalle <= 0 || !$tipo) {
                echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
                return;
            }

            $table = "tb_comprobantes_" . $tipo."s";
            $validTables = ['tb_comprobantes_compras', 'tb_comprobantes_transportes', 'tb_comprobantes_viaticos'];
            if (!in_array($table, $validTables)) {
                echo json_encode(['success' => false, 'error' => 'Tipo de detalle no válido']);
                return;
            }

            try {
                $query = "SELECT * FROM $table WHERE id_rendicion = :id_rendicion AND id_detalle = :id_detalle";
                $stmt = $this->db->prepare($query);
                $stmt->execute([':id_rendicion' => $id_rendicion, ':id_detalle' => $id_detalle]);
                $comprobantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode(['success' => true, 'comprobantes' => $comprobantes]);
            } catch (PDOException $e) {
                error_log('Error al obtener comprobantes: ' . $e->getMessage());
                echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
            }
        }
    }

    public function guardarComprobante_compra() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_rendicion = $_POST['id_rendicion'] ?? '';
        $id_detalle = $_POST['id_detalle'] ?? '';
        $tipo_comprobante = $_POST['tipo_comprobante'] ?? '';
        $ruc_emisor = $_POST['ruc_emisor'] ?? '';
        $serie_numero = $_POST['serie_numero'] ?? '';
        $doc_receptor = $_POST['doc_receptor'] ?? '';
        $fecha_emision = $_POST['fecha_emision'] ?? '';
        $importe_total = $_POST['importe_total'] ?? '0.00';
        $archivo = $_FILES['archivo'] ?? null;

        if (!$id_rendicion || !$id_detalle || !$tipo_comprobante || !$ruc_emisor || !$serie_numero || !$doc_receptor || !$fecha_emision || !$importe_total) {
            echo json_encode(['success' => false, 'error' => 'Parámetros inválidos, favor de completar todos los campos']);
            return;
        }

        try {
            $this->db->beginTransaction();

            $archivo_path = null;
            if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
                $targetDir = "uploads/";
                if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
                $fileName = uniqid() . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($archivo['tmp_name'], $targetFile)) {
                    $archivo_path = $fileName;
                } else {
                    throw new Exception('Error al subir el archivo');
                }
            }

            $query_comprobante = "INSERT INTO tb_comprobantes_compras (id_rendicion, id_detalle, tipo_comprobante, ruc_emisor, serie_numero, doc_receptor, fecha_emision, importe_total, archivo, nombre_archivo) VALUES (:id_rendicion, :id_detalle, :tipo_comprobante, :ruc_emisor, :serie_numero, :doc_receptor, :fecha_emision, :importe_total, :archivo, :nombre_archivo)";
            $stmt_comprobante = $this->db->prepare($query_comprobante);
            $success = $stmt_comprobante->execute([
                ':id_rendicion' => $id_rendicion,
                ':id_detalle' => $id_detalle,
                ':tipo_comprobante' => $tipo_comprobante,
                ':ruc_emisor' => $ruc_emisor,
                ':serie_numero' => $serie_numero,
                ':doc_receptor' => $doc_receptor,
                ':fecha_emision' => $fecha_emision,
                ':importe_total' => $importe_total,
                ':archivo' => $archivo_path,
                ':nombre_archivo' => $archivo && isset($archivo['name']) ? $archivo['name'] : ''
            ]);

            if (!$success) {
                throw new Exception('Fallo en la ejecución del INSERT: ' . print_r($stmt_comprobante->errorInfo(), true));
            }

            $lastInsertId = $this->db->lastInsertId();
            if ($lastInsertId === 0) {
                throw new Exception('No se generó un ID válido después del INSERT');
            }


            $this->db->commit();
            $this->actualizarMontoRendido($id_rendicion);
            $monto_rendido = $this->rendicionesModel->getMontoTotalRendidoByRendicion($id_rendicion);
            error_log("Último ID insertado: $lastInsertId"); // Depuración
            echo json_encode(['success' => true, 'monto_rendido' => $monto_rendido, 'id' => $lastInsertId, 'archivo' => $archivo_path]);
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error al guardar comprobante: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}

    private function actualizarMontoRendido($id_rendicion) {
        $monto_total = $this->rendicionesModel->getMontoTotalRendidoByRendicion($id_rendicion);
        $query = "UPDATE tb_rendiciones SET monto_rendido = :monto_rendido WHERE id = :id_rendicion";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':monto_rendido' => $monto_total, ':id_rendicion' => $id_rendicion]);
    }

    public function guardarComprobante_viatico() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rendicion = $_POST['id_rendicion'] ?? '';
            $id_detalle = $_POST['id_detalle'] ?? '';
            $tipo_comprobante = $_POST['tipo_comprobante'] ?? '';
            $ruc_emisor = $_POST['ruc_emisor'] ?? '';
            $serie_numero = $_POST['serie_numero'] ?? '';
            $doc_receptor = $_POST['doc_receptor'] ?? '';
            $fecha_emision = $_POST['fecha_emision'] ?? '';
            $importe_total = $_POST['importe_total'] ?? '0.00';
            $archivo = $_FILES['archivo'] ?? null;

            if (!$id_rendicion || !$id_detalle || !$tipo_comprobante || !$ruc_emisor || !$serie_numero || !$doc_receptor || !$fecha_emision || !$importe_total) {
                echo json_encode(['success' => false, 'error' => 'Parámetros inválidos, favor de completar todos los campos']);
                return;
            }

            try {
                $this->db->beginTransaction();

                $archivo_path = null;
                if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
                    $targetDir = "uploads/";
                    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
                    $fileName = uniqid() . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                    $targetFile = $targetDir . $fileName;
                    if (move_uploaded_file($archivo['tmp_name'], $targetFile)) {
                        $archivo_path = $fileName;
                    } else {
                        throw new Exception('Error al subir el archivo');
                    }
                }

                $query_comprobante = "INSERT INTO tb_comprobantes_viaticos (id_rendicion, id_detalle, tipo_comprobante, ruc_emisor, serie_numero, doc_receptor, fecha_emision, importe_total, archivo, nombre_archivo) VALUES (:id_rendicion, :id_detalle, :tipo_comprobante, :ruc_emisor, :serie_numero, :doc_receptor, :fecha_emision, :importe_total, :archivo, :nombre_archivo)";
                $stmt_comprobante = $this->db->prepare($query_comprobante);
                $success = $stmt_comprobante->execute([
                    ':id_rendicion' => $id_rendicion,
                    ':id_detalle' => $id_detalle,
                    ':tipo_comprobante' => $tipo_comprobante,
                    ':ruc_emisor' => $ruc_emisor,
                    ':serie_numero' => $serie_numero,
                    ':doc_receptor' => $doc_receptor,
                    ':fecha_emision' => $fecha_emision,
                    ':importe_total' => $importe_total,
                    ':archivo' => $archivo_path,
                    ':nombre_archivo' => $archivo && isset($archivo['name']) ? $archivo['name'] : ''
                ]);

                if (!$success) {
                    throw new Exception('Fallo en la ejecución del INSERT: ' . print_r($stmt_comprobante->errorInfo(), true));
                }

                $lastInsertId = $this->db->lastInsertId();
                if ($lastInsertId === 0) {
                    throw new Exception('No se generó un ID válido después del INSERT');
                }

                $this->db->commit();
                $this->actualizarMontoRendido($id_rendicion);
                $monto_rendido = $this->rendicionesModel->getMontoTotalRendidoByRendicion($id_rendicion);
                error_log("Último ID insertado: $lastInsertId"); // Depuración
                echo json_encode(['success' => true, 'monto_rendido' => $monto_rendido, 'id' => $lastInsertId, 'archivo' => $archivo_path]);
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log('Error al guardar comprobante: ' . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function guardarComprobante_transporte() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rendicion = $_POST['id_rendicion'] ?? '';
            $id_detalle = $_POST['id_detalle'] ?? '';
            $tipo_comprobante = $_POST['tipo_comprobante'] ?? '';
            $ruc_emisor = $_POST['ruc_emisor'] ?? '';
            $serie_numero = $_POST['serie_numero'] ?? '';
            $doc_receptor = $_POST['doc_receptor'] ?? '';
            $fecha_emision = $_POST['fecha_emision'] ?? '';
            $importe_total = $_POST['importe_total'] ?? '0.00';
            $archivo = $_FILES['archivo'] ?? null;

            if (!$id_rendicion || !$id_detalle || !$tipo_comprobante || !$ruc_emisor || !$serie_numero || !$doc_receptor || !$fecha_emision || !$importe_total) {
                echo json_encode(['success' => false, 'error' => 'Parámetros inválidos, favor de completar todos los campos']);
                return;
            }

            try {
                $this->db->beginTransaction();

                $archivo_path = null;
                if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
                    $targetDir = "uploads/";
                    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
                    $fileName = uniqid() . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                    $targetFile = $targetDir . $fileName;
                    if (move_uploaded_file($archivo['tmp_name'], $targetFile)) {
                        $archivo_path = $fileName;
                    } else {
                        throw new Exception('Error al subir el archivo');
                    }
                }

                $query_comprobante = "INSERT INTO tb_comprobantes_transportes (id_rendicion, id_detalle, tipo_comprobante, ruc_emisor, serie_numero, doc_receptor, fecha_emision, importe_total, archivo, nombre_archivo) VALUES (:id_rendicion, :id_detalle, :tipo_comprobante, :ruc_emisor, :serie_numero, :doc_receptor, :fecha_emision, :importe_total, :archivo, :nombre_archivo)";
                $stmt_comprobante = $this->db->prepare($query_comprobante);
                $success = $stmt_comprobante->execute([
                    ':id_rendicion' => $id_rendicion,
                    ':id_detalle' => $id_detalle,
                    ':tipo_comprobante' => $tipo_comprobante,
                    ':ruc_emisor' => $ruc_emisor,
                    ':serie_numero' => $serie_numero,
                    ':doc_receptor' => $doc_receptor,
                    ':fecha_emision' => $fecha_emision,
                    ':importe_total' => $importe_total,
                    ':archivo' => $archivo_path,
                    ':nombre_archivo' => $archivo && isset($archivo['name']) ? $archivo['name'] : ''
                ]);

                if (!$success) {
                    throw new Exception('Fallo en la ejecución del INSERT: ' . print_r($stmt_comprobante->errorInfo(), true));
                }

                $lastInsertId = $this->db->lastInsertId();
                if ($lastInsertId === 0) {
                    throw new Exception('No se generó un ID válido después del INSERT');
                }

                $this->db->commit();
                $this->actualizarMontoRendido($id_rendicion);
                $monto_rendido = $this->rendicionesModel->getMontoTotalRendidoByRendicion($id_rendicion);
                error_log("Último ID insertado: $lastInsertId"); // Depuración
                echo json_encode(['success' => true, 'monto_rendido' => $monto_rendido, 'id' => $lastInsertId, 'archivo' => $archivo_path]);
            } catch (Exception $e) {
                $this->db->rollBack();
                error_log('Error al guardar comprobante: ' . $e->getMessage());
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        }
    }

    public function updateComprobante_compra() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            return;
        }

        $id = $_POST['id'] ?? '';
        $id_rendicion = $_POST['id_rendicion'] ?? '';
        $id_detalle = $_POST['id_detalle'] ?? '';
        $tipo_comprobante = $_POST['tipo_comprobante'] ?? '';
        $ruc_emisor = $_POST['ruc_emisor'] ?? '';
        $serie_numero = $_POST['serie_numero'] ?? '';
        $doc_receptor = $_POST['doc_receptor'] ?? '';
        $fecha_emision = $_POST['fecha_emision'] ?? '';
        $importe_total = $_POST['importe_total'] ?? '0.00';
        $archivo = $_FILES['archivo'] ?? null;
        $archivo_subido_exitoso = false;
        //error_log(print_r($archivo, true));

        if (!$id_rendicion || !$id_detalle || !$tipo_comprobante || !$ruc_emisor || !$serie_numero || !$doc_receptor || !$fecha_emision || !$importe_total || !$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
            return;
        }

        try {
            $archivo_path = null;
            $nombre_archivo_original = null;
            
            if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
                error_log("Archivo recibido: " . print_r($archivo, true)); // Depuración
                $targetDir = "uploads/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                    error_log("Directorio uploads/ creado");
                }
                $fileName = uniqid() . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($archivo['tmp_name'], $targetFile)) {
                    $archivo_path = $fileName;
                    $nombre_archivo_original = $archivo['name'];
                    $archivo_subido_exitoso = true; // Establece esta bandera en true
                    error_log("Archivo guardado en: $targetFile");
                } else {
                    throw new Exception('Error al mover el archivo subido. Código de error: ' . $archivo['error']);
                }
            } else {
                error_log("No se recibió archivo o error en la subida: " . ($archivo ? $archivo['error'] : 'null'));
            }

             $query = "UPDATE tb_comprobantes_compras SET 
                    id_rendicion = :id_rendicion, 
                    id_detalle = :id_detalle, 
                    tipo_comprobante = :tipo_comprobante, 
                    ruc_emisor = :ruc_emisor, 
                    serie_numero = :serie_numero, 
                    doc_receptor = :doc_receptor, 
                    fecha_emision = :fecha_emision, 
                    importe_total = :importe_total";

            if ($archivo_subido_exitoso) {
                $query .= ", archivo = :archivo, nombre_archivo = :nombre_archivo";
            }

            $query .= " WHERE id = :id";

            //$query = "UPDATE tb_comprobantes_compras SET id_rendicion = :id_rendicion, id_detalle = :id_detalle, tipo_comprobante = :tipo_comprobante, ruc_emisor = :ruc_emisor, serie_numero = :serie_numero, doc_receptor = :doc_receptor, fecha_emision = :fecha_emision, importe_total = :importe_total, archivo = :archivo, nombre_archivo = :nombre_archivo WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $params = [
                ':id' => $id,
                ':id_rendicion' => $id_rendicion,
                ':id_detalle' => $id_detalle,
                ':tipo_comprobante' => $tipo_comprobante,
                ':ruc_emisor' => $ruc_emisor,
                ':serie_numero' => $serie_numero,
                ':doc_receptor' => $doc_receptor,
                ':fecha_emision' => $fecha_emision,
                ':importe_total' => $importe_total,
            ];

            if ($archivo_subido_exitoso) {
                $params[':archivo'] = $archivo_path;
                $params[':nombre_archivo'] = $nombre_archivo_original;
            }

            $stmt->execute($params);

            $this->actualizarMontoRendido($id_rendicion);
            $monto_rendido = $this->rendicionesModel->getMontoTotalRendidoByRendicion($id_rendicion);
            error_log("Datos enviados como respuesta: " . json_encode(['success' => true, 'monto_rendido' => $monto_rendido]));
            echo json_encode(['success' => true, 'monto_rendido' => $monto_rendido]);
            error_log("Comprobante ID $id actualizado exitosamente para rendición $id_rendicion");
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('Error al actualizar comprobante: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log('Error al procesar comprobante: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
        }
    }

    // Añade métodos similares para updateComprobante_viatico y updateComprobante_transporte
    public function updateComprobante_viatico() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            return;
        }

        $id = $_POST['id'] ?? '';
        $id_rendicion = $_POST['id_rendicion'] ?? '';
        $id_detalle = $_POST['id_detalle'] ?? '';
        $tipo_comprobante = $_POST['tipo_comprobante'] ?? '';
        $ruc_emisor = $_POST['ruc_emisor'] ?? '';
        $serie_numero = $_POST['serie_numero'] ?? '';
        $doc_receptor = $_POST['doc_receptor'] ?? '';
        $fecha_emision = $_POST['fecha_emision'] ?? '';
        $importe_total = $_POST['importe_total'] ?? '0.00';
        $archivo = $_FILES['archivo'] ?? null;
        $archivo_subido_exitoso = false;

        if (!$id_rendicion || !$id_detalle || !$tipo_comprobante || !$ruc_emisor || !$serie_numero || !$doc_receptor || !$fecha_emision || !$importe_total || !$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
            return;
        }

        try {
            $archivo_path = null;
            $nombre_archivo_original = null;

            if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
                $targetDir = "uploads/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $fileName = uniqid() . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($archivo['tmp_name'], $targetFile)) {
                    $archivo_path = $fileName;
                    $nombre_archivo_original = $archivo['name'];
                    $archivo_subido_exitoso = true;
                } else {
                    throw new Exception('Error al mover el archivo subido');
                }
            }

            $query = "UPDATE tb_comprobantes_viaticos SET 
                    id_rendicion = :id_rendicion, 
                    id_detalle = :id_detalle, 
                    tipo_comprobante = :tipo_comprobante, 
                    ruc_emisor = :ruc_emisor, 
                    serie_numero = :serie_numero, 
                    doc_receptor = :doc_receptor, 
                    fecha_emision = :fecha_emision, 
                    importe_total = :importe_total";

            if ($archivo_subido_exitoso) {
                $query .= ", archivo = :archivo, nombre_archivo = :nombre_archivo";
            }

            $query .= " WHERE id = :id";

            $stmt = $this->db->prepare($query);
            $params = [
                ':id' => $id,
                ':id_rendicion' => $id_rendicion,
                ':id_detalle' => $id_detalle,
                ':tipo_comprobante' => $tipo_comprobante,
                ':ruc_emisor' => $ruc_emisor,
                ':serie_numero' => $serie_numero,
                ':doc_receptor' => $doc_receptor,
                ':fecha_emision' => $fecha_emision,
                ':importe_total' => $importe_total,
            ];

            if ($archivo_subido_exitoso) {
                $params[':archivo'] = $archivo_path;
                $params[':nombre_archivo'] = $nombre_archivo_original;
            }

            $stmt->execute($params);

            $this->actualizarMontoRendido($id_rendicion);
            $monto_rendido = $this->rendicionesModel->getMontoTotalRendidoByRendicion($id_rendicion);
            echo json_encode(['success' => true, 'monto_rendido' => $monto_rendido]);
            error_log("Comprobante ID $id actualizado exitosamente para rendición $id_rendicion");
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('Error al actualizar comprobante: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log('Error al procesar comprobante: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
        }
    }

    public function updateComprobante_transporte() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            return;
        }

        $id = $_POST['id'] ?? '';
        $id_rendicion = $_POST['id_rendicion'] ?? '';
        $id_detalle = $_POST['id_detalle'] ?? '';
        $tipo_comprobante = $_POST['tipo_comprobante'] ?? '';
        $ruc_emisor = $_POST['ruc_emisor'] ?? '';
        $serie_numero = $_POST['serie_numero'] ?? '';
        $doc_receptor = $_POST['doc_receptor'] ?? '';
        $fecha_emision = $_POST['fecha_emision'] ?? '';
        $importe_total = $_POST['importe_total'] ?? '0.00';
        $archivo = $_FILES['archivo'] ?? null;
        $archivo_subido_exitoso = false;

        if (!$id_rendicion || !$id_detalle || !$tipo_comprobante || !$ruc_emisor || !$serie_numero || !$doc_receptor || !$fecha_emision || !$importe_total || !$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Parámetros inválidos']);
            return;
        }

        try {
            $archivo_path = null;
            $nombre_archivo_original = null;

            if ($archivo && $archivo['error'] === UPLOAD_ERR_OK) {
                $targetDir = "uploads/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $fileName = uniqid() . '.' . strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $targetFile = $targetDir . $fileName;
                if (move_uploaded_file($archivo['tmp_name'], $targetFile)) {
                    $archivo_path = $fileName;
                    $nombre_archivo_original = $archivo['name'];
                    $archivo_subido_exitoso = true;
                } else {
                    throw new Exception('Error al mover el archivo subido');
                }
            }

            $query = "UPDATE tb_comprobantes_transportes SET 
                    id_rendicion = :id_rendicion, 
                    id_detalle = :id_detalle, 
                    tipo_comprobante = :tipo_comprobante, 
                    ruc_emisor = :ruc_emisor, 
                    serie_numero = :serie_numero, 
                    doc_receptor = :doc_receptor, 
                    fecha_emision = :fecha_emision, 
                    importe_total = :importe_total";

            if ($archivo_subido_exitoso) {
                $query .= ", archivo = :archivo, nombre_archivo = :nombre_archivo";
            }

            $query .= " WHERE id = :id";

            $stmt = $this->db->prepare($query);
            $params = [
                ':id' => $id,
                ':id_rendicion' => $id_rendicion,
                ':id_detalle' => $id_detalle,
                ':tipo_comprobante' => $tipo_comprobante,
                ':ruc_emisor' => $ruc_emisor,
                ':serie_numero' => $serie_numero,
                ':doc_receptor' => $doc_receptor,
                ':fecha_emision' => $fecha_emision,
                ':importe_total' => $importe_total,
            ];

            if ($archivo_subido_exitoso) {
                $params[':archivo'] = $archivo_path;
                $params[':nombre_archivo'] = $nombre_archivo_original;
            }

            $stmt->execute($params);

            $this->actualizarMontoRendido($id_rendicion);
            $monto_rendido = $this->rendicionesModel->getMontoTotalRendidoByRendicion($id_rendicion);
            echo json_encode(['success' => true, 'monto_rendido' => $monto_rendido]);
            error_log("Comprobante ID $id actualizado exitosamente para rendición $id_rendicion");
        } catch (PDOException $e) {
            http_response_code(500);
            error_log('Error al actualizar comprobante: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log('Error al procesar comprobante: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
        }
    }

    private function calcularMontoRendido($id_rendicion, $tipo) {
        $table = "tb_comprobantes_" . $tipo."s";
        $query = "SELECT SUM(importe_total) as total FROM $table WHERE id_rendicion = :id_rendicion";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id_rendicion' => $id_rendicion]);
        return $stmt->fetchColumn() ?: 0;
    }

    private function uploadFile($file) {
        $targetDir = "uploads/";
        $fileName = uniqid() . '.' . strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($file['tmp_name'], $targetFile);
        return $fileName;
    }


    /* Aquí termina la nueva integración para poder registrar los comprobantes de rendiciones  */ 
}