<?php
require_once 'src/config/Database.php';
require_once 'src/models/PresupuestoSsccModel.php';

date_default_timezone_set('America/Lima'); // Ajusta según tu región (-05:00)

class PresupuestoSSccController {
    private $db;
    private $presupuestoSsccModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->presupuestoSsccModel = new PresupuestoSsccModel();
    }

    public function index(){
        if (!isset($_SESSION['id'])) {
            header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
            exit;
        }

        $presupuestos = $this->presupuestoSsccModel->getAllPresupuestos();
        require_once 'src/views/presupuestos.php';
    }

     public function get_ccs() {
        header('Content-Type: application/json');
        echo json_encode($this->presupuestoSsccModel->getAllCC());
        exit;
    }

    public function get_sccs() {
        header('Content-Type: application/json');
        $cc_id = $_GET['cc_id'] ?? '';
        error_log($cc_id);
        echo json_encode($this->presupuestoSsccModel->getSCCsByCC($cc_id));
        exit;
    }

    public function get_ssccs() {
        header('Content-Type: application/json');
        $scc_id = $_GET['scc_id'] ?? '';
        error_log($scc_id);
        echo json_encode($scc_id ? $this->presupuestoSsccModel->getAvailableSSCCsBySCC($scc_id) : []);
        exit;
    }

    public function add() {
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $codigo_sscc = $_POST['sscc_codigo'] ?? '';
                $saldo_inicial = $_POST['saldo_inicial'] ?? 0;
                $saldo_final = $_POST['saldo_final'] ?? 0;
                $saldo_disponible = $_POST['saldo_disponible'] ?? 0;
                //$ultima_actualizacion = $_POST['ultima_actualizacion'] ?? date('Y-m-d H:i:s');
                $activo = 1;

                if (!$codigo_sscc || $saldo_inicial <= 0) {
                    $response['message'] = 'El SSCC y el saldo inicial son obligatorios.';
                } else {
                    if ($this->presupuestoSsccModel->addPresupuesto($codigo_sscc, $saldo_inicial, $saldo_final, $saldo_disponible, null, $activo)) {
                        $id_presupuesto = $this->presupuestoSsccModel->getLastInsertId();
                        $id_usuario = $_SESSION['id'] ?? 0;
                        $fecha = date('Y-m-d H:i:s');
                        $tipo_movimiento = 1; // Creación
                        $monto = $saldo_inicial;
                        $comentario = 'creación';

                        if ($this->presupuestoSsccModel->addMovimientoPresupuesto($id_presupuesto, $tipo_movimiento, $monto, $id_usuario, $fecha, $comentario)) {
                            $response['success'] = true;
                            $response['message'] = 'Presupuesto registrado con éxito';
                        } else {
                            $response['message'] = 'Error al registrar el movimiento del presupuesto.';
                        }
                    } else {
                        $response['message'] = 'Error al registrar el presupuesto.';
                    }
                }
            } catch (Exception $e) {
                $response['message'] = 'Ocurrió un error inesperado: ' . $e->getMessage();
            }
        }
        echo json_encode($response);
        exit;
    }

    public function add_funds() {
        header('Content-Type: application/json');
        $response = ['success' => false, 'message' => ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $presupuestoId = $_POST['presupuestoId'] ?? 0;
                $montoAbono = floatval($_POST['montoAbono'] ?? 0);
                $id_usuario = $_SESSION['id'] ?? 0;

                error_log("Recibidos: presupuestoId=$presupuestoId, montoAbono=$montoAbono, id_usuario=$id_usuario");

                if (!$presupuestoId || $montoAbono <= 0) {
                    $response['message'] = 'El ID del presupuesto y el monto a añadir son obligatorios./';
                } else {
                    if ($this->presupuestoSsccModel->addFunds($presupuestoId, $montoAbono, $id_usuario)) {
                        $response['success'] = true;
                        $response['message'] = 'Fondos añadidos con éxito.';
                    } else {
                        $response['message'] = 'Error al añadir fondos.';
                    }
                }
            } catch (Exception $e) {
                error_log("Error en add_funds: " . $e->getMessage());
                $response['message'] = 'Ocurrió un error inesperado: ' . $e->getMessage();
            }
        }

        echo json_encode($response);
        exit;
    }
}