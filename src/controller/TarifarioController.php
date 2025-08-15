<?php
require_once 'src/config/Database.php';
require_once 'src/models/UserModel.php';
require_once 'src/models/TarifarioModel.php';

class TarifarioController {
    private $db;
    private $userModel;
    private $tarifarioModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->userModel = new UserModel();
        $this->tarifarioModel = new TarifarioModel();
    }

    public function index(){
        // $cargos = $this->tarifarioModel->getCargosTarifario();
        $allCargos = $this->tarifarioModel->getAllCargosTarifario();
        $categorias = $this->tarifarioModel->getCategoriasTarifario();
        $tarifario = $this->tarifarioModel->getTarifario();
        if ($_SESSION['rol'] != 1 && $_SESSION['rol'] != 4) {
            header('Location: iniciar_sesion');
            exit;
        }
        require_once 'src/views/tarifario.php';
    }

    public function obtenerCargos() {
        $cargos = $this->tarifarioModel->getCargosTarifario();
        header('Content-Type: application/json');
        echo json_encode($cargos);
    }

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

    public function crearCargo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $nombre = $_POST['nombre'] ?? '';
        $montos = json_decode($_POST['montos'], true) ?? [];

        error_log('Datos recibidos: nombre=' . $nombre . ', montos=' . print_r($montos, true)); // Depuración

        if (empty($nombre) || empty($montos)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            return;
        }

        try {
            // Insertar el nuevo cargo
            $cargoId = $this->tarifarioModel->insertCargo($nombre);
            if ($cargoId === false) {
                throw new Exception('Error al crear el cargo');
            }

            // Insertar los montos en tb_tarifario
            $categorias = $this->tarifarioModel->getCategoriasTarifario();
            foreach ($categorias as $categoria) {
                $conceptoId = $categoria['id'];
                $monto = isset($montos[$conceptoId]) ? floatval($montos[$conceptoId]) : 0.00;
                error_log("Insertando: cargo_id=$cargoId, concepto_id=$conceptoId, monto=$monto"); // Depuración
                if ($this->tarifarioModel->insertTarifario($cargoId, $conceptoId, $monto) === false) {
                    throw new Exception('Error al insertar monto para categoría ' . $categoria['nombre']);
                }
            }

            $_SESSION['success'] = 'Cargo y tarifario creados exitosamente';
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'cargo_id' => $cargoId]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear el cargo: ' . $e->getMessage()]);
        }
    }

    // Funcionalidades para edición del tarifario
    public function obtenerMontosParaEditar() {
        if (!isset($_GET['cargo_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'cargo_id no proporcionado']);
            return;
        }

        $cargoId = intval($_GET['cargo_id']);

        //here
        if (!isset($cargoId) || !is_numeric($cargoId)) {
            http_response_code(400);
            echo json_encode(['error' => 'cargo_id no válido']);
            return;
        }

        $montos = $this->tarifarioModel->getMontosPorCargo($cargoId);
        $activo = $this->tarifarioModel->getCargoEstado($cargoId);
        $response = [
            'montos' => $montos,
            'activo' => $activo
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function obtenerCategorias() {
        $categorias = $this->tarifarioModel->getCategoriasTarifario();
        header('Content-Type: application/json');
        echo json_encode($categorias);
    }

    public function obtenerDetallesTarifario() {
        if (!isset($_GET['tarifario_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'tarifario_id no proporcionado']);
            return;
        }

        $tarifarioId = intval($_GET['tarifario_id']);
        $detalle = $this->tarifarioModel->getDetallesTarifario($tarifarioId);

        if (!$detalle) {
            http_response_code(404);
            echo json_encode(['error' => 'Registro no encontrado']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode($detalle);
    }

    public function actualizarMontos() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $tarifarioId = $_POST['tarifario_id'] ?? null;
        $monto = $_POST['monto'] ?? null;

        error_log('Datos recibidos: tarifario_id=' . $tarifarioId . ', monto=' . $monto);

        if (!$tarifarioId || $monto === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            return;
        }

        try {
            if ($this->tarifarioModel->updateMontoTarifario($tarifarioId, $monto)) {
                $_SESSION['success'] = 'Monto actualizado exitosamente';
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('No se encontró el registro o no se realizó ningún cambio');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

}