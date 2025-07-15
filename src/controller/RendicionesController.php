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

}