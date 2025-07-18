<?php
require_once 'src/config/Database.php';
require_once 'src/models/RendicionesModel.php';

class RendicionesController {
    private $db;
    private $rendicionesModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->rendicionesModel = new RendicionesModel();
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

    public function getDetallesRendidosByRendicion() {
        header('Content-Type: application/json');
        if (isset($_GET['id_rendicion'])) {
            $id_rendicion = $_GET['id_rendicion'];
            $detalles = $this->rendicionesModel->getDetallesRendidosByRendicion($id_rendicion);
            echo json_encode($detalles);
        } else {
            echo json_encode(['error' => 'No se proporcionó el id de la rendición']);
        }
        exit;
    }

    public function guardarItemRendido() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rendicion = $_POST['id_rendicion'] ?? null;
            $id_detalle_compra = $_POST['id_detalle_compra'] ?? null;
            if ($id_rendicion && $id_detalle_compra) {
                $montoRendido = floatval($_POST['montoRendido']);
                $fecha = $_POST['fecha'];
                $archivoNombre = $_FILES['archivo']['name'] ?? null;

                $success = $this->rendicionesModel->guardarItemRendido($id_rendicion, $id_detalle_compra, $montoRendido, $fecha, $archivoNombre);
                if ($success) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Error al guardar el ítem']);
                }
            } else {
                echo json_encode(['error' => 'Datos incompletos']);
            }
        } else {
            echo json_encode(['error' => 'Método no permitido']);
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

    public function getDetallesViajesRendidosByRendicion() {
        header('Content-Type: application/json');
        if (isset($_GET['id_rendicion'])) {
            $id_rendicion = $_GET['id_rendicion'];
            $detalles = $this->rendicionesModel->getDetallesViajesRendidosByRendicion($id_rendicion);
            echo json_encode($detalles);
        } else {
            echo json_encode(['error' => 'No se proporcionó el id de la rendición']);
        }
        exit;
    }

    public function guardarItemViaje() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rendicion = $_POST['id_rendicion'] ?? null;
            $id_detalle_viaje = $_POST['id_detalle_viaje'] ?? null;
            if ($id_rendicion && $id_detalle_viaje) {
                $montoRendido = floatval($_POST['montoRendido']);
                $fecha = $_POST['fecha'];
                $archivoNombre = $_FILES['archivo']['name'] ?? null;
                $success = $this->rendicionesModel->guardarItemViaje($id_rendicion, $id_detalle_viaje, $montoRendido, $fecha, $archivoNombre);
                if ($success) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Error al guardar el ítem de viático']);
                }
            } else {
                echo json_encode(['error' => 'Datos incompletos']);
            }
        } else {
            echo json_encode(['error' => 'Método no permitido']);
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

    public function getDetallesTransportesRendidosByRendicion() {
        header('Content-Type: application/json');
        if (isset($_GET['id_rendicion'])) {
            $id_rendicion = $_GET['id_rendicion'];
            $detalles = $this->rendicionesModel->getDetallesTransportesRendidosByRendicion($id_rendicion);
            echo json_encode($detalles);
        } else {
            echo json_encode(['error' => 'No se proporcionó el id de la rendición']);
        }
        exit;
    }

    public function guardarItemTransporte() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_rendicion = $_POST['id_rendicion'] ?? null;
            $id_transporte_provincial = $_POST['id_transporte_provincial'] ?? null;
            if ($id_rendicion && $id_transporte_provincial) {
                $montoRendido = floatval($_POST['montoRendido']);
                $fecha = $_POST['fecha'];
                $archivoNombre = $_FILES['archivo']['name'] ?? null;
                $success = $this->rendicionesModel->guardarItemTransporte($id_rendicion, $id_transporte_provincial, $montoRendido, $fecha, $archivoNombre);
                if ($success) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['error' => 'Error al guardar el ítem de transporte']);
                }
            } else {
                echo json_encode(['error' => 'Datos incompletos']);
            }
        } else {
            echo json_encode(['error' => 'Método no permitido']);
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
        if (isset($_GET['id_rendicion'])) {
            $monto = $this->rendicionesModel->getMontoTotalRendidoByRendicion($_GET['id_rendicion']);
            echo json_encode($monto);
        } else {
            echo json_encode(0.00);
        }
        exit;
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
        if (isset($_POST['id_rendicion']) && isset($_POST['id_usuario'])) {
            $model = new RendicionesModel();
            $success = $model->aprobarRendicion($_POST['id_rendicion'], $_POST['id_usuario']);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'error' => $success ? '' : 'Error al aprobar']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
        }
        exit;
    }

    public function observarRendicion() {
        $id_rendicion = $_POST['id_rendicion'];
        $id_usuario = $_POST['id_usuario'];
        $comentario = $_POST['comentario'] ?? 'Sin comentario';

        $model = new RendicionesModel();
        $result = $model->observarRendicion($id_rendicion, $id_usuario, $comentario);

        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }

    public function cerrarRendicion() {
        $id_rendicion = $_POST['id_rendicion'];
        $id_usuario = $_POST['id_usuario'];
        $comentario = $_POST['comentario'] ?? 'Rendición cerrada';
        $id_anticipo = $_POST['id_anticipo'];

        $model = new RendicionesModel();
        $result = $model->cerrarRendicion($id_rendicion, $id_usuario, $comentario, $id_anticipo);

        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
}