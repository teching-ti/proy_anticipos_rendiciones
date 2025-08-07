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

    public function index(){
        // $cargos = $this->tarifarioModel->getCargosTarifario();
        $allCargos = $this->tarifarioModel->getAllCargosTarifario();
        $categorias = $this->tarifarioModel->getCategoriasTarifario();
        $tarifario = $this->tarifarioModel->getTarifario();
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

    // public function actualizarMontos() {
    // if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //     http_response_code(405);
    //     echo json_encode(['error' => 'Método no permitido']);
    //     return;
    // }

    // $cargoId = $_POST['cargo_id'] ?? null;
    // $montos = $_POST['montos'] ?? '';
    // $activo = isset($_POST['activo']) ? filter_var($_POST['activo'], FILTER_VALIDATE_BOOLEAN) : null;

    // error_log('Datos recibidos (crudos): cargo_id=' . $cargoId . ', montos=' . $montos . ', activo=' . $activo); // Depuración

    // if (!$cargoId || empty($montos) || $activo === null) {
    //     http_response_code(400);
    //     echo json_encode(['error' => 'Datos incompletos']);
    //     return;
    // }

    // // Decodificar los montos asegurando que sea un array
    // $montosArray = json_decode($montos, true);
    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         error_log('Error al decodificar montos: ' . json_last_error_msg() . ', valor recibido: ' . $montos);
    //         http_response_code(400);
    //         echo json_encode(['error' => 'Formato de montos inválido: ' . json_last_error_msg()]);
    //         return;
    //     }

    //     if (!is_array($montosArray)) {
    //         error_log('Montos decodificados no es un array: ' . print_r($montosArray, true));
    //         http_response_code(400);
    //         echo json_encode(['error' => 'Montos no es un array válido']);
    //         return;
    //     }

    //     try {
    //         if ($this->tarifarioModel->updateTarifario($cargoId, $montosArray)) {
    //             if ($this->tarifarioModel->updateCargoEstado($cargoId, $activo)) {
    //                 $_SESSION['success'] = 'Montos y estado actualizados exitosamente';
    //                 echo json_encode(['success' => true]);
    //             } else {
    //                 throw new Exception('Error al actualizar el estado del cargo');
    //             }
    //         } else {
    //             throw new Exception('Error al actualizar los montos');
    //         }
    //     } catch (Exception $e) {
    //         http_response_code(500);
    //         echo json_encode(['error' => 'Error al actualizar: ' . $e->getMessage()]);
    //     }
    // }

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