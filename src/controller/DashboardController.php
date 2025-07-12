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

    // private function connectExternal() {
    //     try {
    //         $dsn = "mysql:host=127.0.0.1;dbname=db_sst_hsqe;charset=utf8mb4";
    //         $user = "root";
    //         $pass = "";
    //         $conn = new PDO($dsn, $user, $pass);
    //         $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //         $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //         return $conn;
    //     } catch (PDOException $e) {
    //         echo 'Error en conexión externa: ' . $e->getMessage();
    //         return null;
    //     }
    // }

    public function index() {
        if (!isset($_SESSION['dni'])) {
            header('Location: iniciar_sesion');
            exit;
        }

        $nombre_usuario = $_SESSION['trabajador']['nombres'] . ' ' . $_SESSION['trabajador']['apellidos'];
        $rol_nombre = $_SESSION['rol_nombre'] ?? 'Sin rol';

        $id = $_SESSION['id'];

        $cantidad_anticipos = $this->anticipoModel->getCountAllAnticiposById($id);
        $cantidad_rendido = $this->anticipoModel->getCountAnticiposByState($id, 'Rendido');
        $cantidad_observado = $this->anticipoModel->getCountAnticiposByState($id, 'Observado');
        $cantidad_autorizado = $this->anticipoModel->getCountAnticiposByState($id, 'Autorizado');

        require_once 'src/views/dashboard.php';
    }
}