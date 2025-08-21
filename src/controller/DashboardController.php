<?php
require_once 'src/config/Database.php';
require_once 'src/models/TrabajadorModel.php';
require_once 'src/models/AnticipoModel.php';

class DashboardController {
    private $db;
    private $trabajadorModel;
    private $anticipoModel;

    public function __construct() {
        // Conexión a la base de datos B (proyecto actual)
        $database = new Database();
        $this->db = $database->connect();
        $this->anticipoModel = new AnticipoModel();
    }

    public function index() {
        if (!isset($_SESSION['dni'])) {
            header('Location: iniciar_sesion');
            exit;
        }

        $nombre_usuario = $_SESSION['trabajador']['nombres'] . ' ' . $_SESSION['trabajador']['apellidos'];
        $rol_nombre = $_SESSION['rol_nombre'] ?? 'Sin rol';
        $rol = $_SESSION['rol'];
        $id = $_SESSION['id'];
        $dep = $_SESSION['trabajador']['departamento'];

        // arreglar esta sección
        if($rol==4 || $rol==5){
            $cantidad_anticipos = $this->anticipoModel->getCountAllAnticipos();
            $cantidad_rendido = $this->anticipoModel->getCountAllAnticiposByState('Rendido');
            $cantidad_observado = $this->anticipoModel->getCountAllAnticiposByState('Observado');
            $cantidad_autorizado = $this->anticipoModel->getCountAllAnticiposByState('Autorizado');
        }else if($rol==3){
            $cantidad_anticipos = $this->anticipoModel->getCountAllAnticiposById($id);
            $cantidad_rendido = $this->anticipoModel->getCountAnticiposByState($id, 'Rendido');
            $cantidad_observado = $this->anticipoModel->getCountAnticiposByState($id, 'Observado');
            $cantidad_autorizado = $this->anticipoModel->getCountAnticiposByState($id, 'Autorizado');
        }else if($rol==2){
            $cantidad_anticipos = $this->anticipoModel->getCountAllAnticiposByDept($dep);
            $cantidad_rendido = $this->anticipoModel->getCountAllAnticiposByStateAndDept('Rendido', $dep);
            $cantidad_observado = $this->anticipoModel->getCountAllAnticiposByStateAndDept( 'Observado', $dep);
            $cantidad_autorizado = $this->anticipoModel->getCountAllAnticiposByStateAndDept('Autorizado', $dep);
        }
        
        require_once 'src/views/dashboard.php';
    }
}