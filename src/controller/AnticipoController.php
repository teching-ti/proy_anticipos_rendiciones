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
        $jefes = $this->anticipoModel->getJefes();
        $sccs = $this->anticipoModel->getAllScc();
        $ssccs = $this->anticipoModel->getAllSscc();
        require_once 'src/views/anticipos.php';
    }

    public function add() {
        session_start();
        if (!isset($_SESSION['id'])) {
            header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
            exit;
        }

        $jefes = $this->anticipoModel->getJefes();
        $sccs = $this->anticipoModel->getAllScc();
        $ssccs = $this->anticipoModel->getAllSscc();
        $id_cat_documento = $this->anticipoModel->getAnticipoDocumentoId();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_usuario = $_SESSION['id'];
            $solicitante = $_SESSION['nombre_usuario'];
            $dni_solicitante = $_SESSION['dni'];
            $area = $_SESSION['trabajador']['departamento'];
            $cargo = $_SESSION['trabajador']['cargo'];
            $codigo_sscc = trim($_POST['codigo_sscc'] ?? '');
            $nombre_proyecto = trim($_POST['nombre_proyecto'] ?? '');
            $fecha_solicitud = trim($_POST['fecha_solicitud'] ?? '');
            $motivo_anticipo = trim($_POST['motivo_anticipo'] ?? '');
            $monto_total_solicitado = (float)($_POST['monto_total_solicitado'] ?? 0);
            $jefe_aprobador = (int)($_POST['jefe_aprobador'] ?? 0) ?: null;

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
                // Verificar relaciones
                $sscc_exists = false;
                foreach ($ssccs as $s) {
                    if ($s['codigo'] === $codigo_sscc) {
                        $sscc_exists = true;
                        break;
                    }
                }
                $jefe_exists = true;
                if ($jefe_aprobador) {
                    $jefe_exists = false;
                    foreach ($jefes as $j) {
                        if ($j['id'] === $jefe_aprobador) {
                            $jefe_exists = true;
                            break;
                        }
                    }
                }
                if (!$sscc_exists) {
                    $_SESSION['error'] = 'Sub-subcentro de costo inválido.';
                    error_log($_SESSION['error']);
                } elseif (!$jefe_exists) {
                    $_SESSION['error'] = 'Jefe aprobador inválido.';
                    error_log($_SESSION['error']);
                /*} elseif ($this->anticipoModel->dniSolicitanteExists($dni_solicitante)) {
                    $_SESSION['error'] = 'El DNI del solicitante ya está registrado en otro anticipo.'; // ESTE ERA EL PROBLEMA!!!! REVISASR
                    error_log($_SESSION['error']);*/
                } else {
                    if ($this->anticipoModel->addAnticipo($id_usuario, $solicitante, $dni_solicitante, $area, $codigo_sscc, $cargo, $nombre_proyecto, $fecha_solicitud, $motivo_anticipo, $monto_total_solicitado, $jefe_aprobador, $id_cat_documento)) {
                        $_SESSION['success'] = 'Anticipo registrado correctamente.';
                    } else {
                        $_SESSION['error'] = 'Error al registrar el anticipo.';
                        error_log($_SESSION['error']);
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

    public function reject() {
        session_start();
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) {
            $_SESSION['error'] = 'No tienes permiso para rechazar anticipos.';
            header('Location: /proy_anticipos_rendiciones/anticipos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = (int)$_POST['id'];
            $comentario = trim($_POST['comentario'] ?? '

Anticipo rechazado');
            if ($this->anticipoModel->updateAnticipoEstado($id, 'Rechazado', $_SESSION['id'], $comentario)) {
                $_SESSION['success'] = 'Anticipo rechazado correctamente.';
            } else {
                $_SESSION['error'] = 'Error al rechazar el anticipo.';
            }
        }
        header('Location: /proy_anticipos_rendiciones/anticipos');
        exit;
    }
}
?>