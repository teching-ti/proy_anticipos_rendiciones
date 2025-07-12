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
}