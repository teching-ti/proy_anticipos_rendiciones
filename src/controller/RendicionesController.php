<?php
require_once 'src/config/Database.php';
require_once 'src/models/TrabajadorModel.php';
require_once 'src/models/RendicionesModel.php';
require_once 'src/config/EmailConfig.php';

class RendicionesController {
    private $db;
    private $rendicionesModel;
    private $emailConfig;

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
                        <p><strong>Observación:</strong> $comentario</p>
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