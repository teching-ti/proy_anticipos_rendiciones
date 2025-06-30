<?php
require_once 'src/config/Database.php';
require_once 'src/models/TarifarioModel.php';

class TarifarioController {
    private $db;
    private $tarifarioModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->tarifarioModel = new TarifarioModel();
    }

    public function obtenerCargos() {
        $model = new TarifarioModel();
        $cargos = $model->getCargosTarifario();
        header('Content-Type: application/json');
        echo json_encode($cargos);
    }

    // public function obtenerMontosPorCargo() {
    //     if (isset($_GET['cargo_id'])) {
    //         $cargoId = intval($_GET['cargo_id']);

    //         $stmt = $this->db->prepare("SELECT concepto_id, monto FROM tb_tarifario WHERE cargo_id = ?");
    //         $stmt->execute([$cargoId]);

    //         $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //         header('Content-Type: application/json');
    //         echo json_encode($result);
    //     } else {
    //         http_response_code(400);
    //         echo json_encode(['error' => 'cargo_id no proporcionado']);
    //     }
    // }

    public function obtenerMontosPorCargo() {
        if (!isset($_GET['cargo_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'cargo_id no proporcionado']);
            return;
        }

        $cargoId = intval($_GET['cargo_id']);

        try {

            $stmt = $this->db->prepare("SELECT t.monto, c.nombre AS concepto
                FROM tb_tarifario t
                JOIN tb_categorias_tarifario c ON t.concepto_id = c.id
                WHERE t.cargo_id = ?");
            $stmt->execute([$cargoId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error en el servidor']);
        }
    }
}