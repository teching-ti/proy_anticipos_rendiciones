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
            header('Location: /proy_anticipos_rendiciones/iniciar_sesion');
            exit;
        }

        require_once 'src/views/rendiciones.php';
    }
}