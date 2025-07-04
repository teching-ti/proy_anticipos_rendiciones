<?php
require_once __DIR__ . '/../../src/model/AnticipoModel.php';

class AnticipoController {
    private $model;

    public function __construct($db) {
        $this->model = new AnticipoModel($db);
    }

    public function getAllScc() {
        header('Content-Type: application/json');
        echo json_encode($this->model->getAllScc());
    }

    public function getSsccByScc() {
        header('Content-Type: application/json');
        if (isset($_GET['codigo_scc'])) {
            echo json_encode($this->model->getSsccByScc($_GET['codigo_scc']));
        } else {
            echo json_encode(['error' => 'Código SCC no proporcionado']);
        }
    }

    public function getAllSscc() {
        header('Content-Type: application/json');
        echo json_encode($this->model->getAllSscc());
    }

    public function getSaldoDisponibleTiempoReal() {
        header('Content-Type: application/json');
        if (isset($_GET['codigo_sscc'])) {
            $saldo = $this->model->getSaldoDisponibleBySscc($_GET['codigo_sscc']);
            echo json_encode(['saldo_disponible' => $saldo]);
        } else {
            echo json_encode(['error' => 'Código SSCC no proporcionado']);
        }
    }

    public function getAnticipoDetails() {
        header('Content-Type: application/json');
        if (isset($_GET['id_anticipo'])) {
            $anticipo = $this->model->getAnticipoById($_GET['id_anticipo']);
            if ($anticipo) {
                echo json_encode($anticipo);
            } else {
                echo json_encode(['error' => 'Anticipo no encontrado']);
            }
        } else {
            echo json_encode(['error' => 'ID de anticipo no proporcionado']);
        }
    }

    public function add() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_usuario' => $_POST['id_usuario'],
                'id_cat_documento' => $_POST['id_cat_documento'],
                'solicitante_nombres' => $_POST['solicitante'],
                'dni_solicitante' => $_POST['dni_solicitante'],
                'departamento' => $_POST['departamento'],
                'departamento_nombre' => $_POST['departamento'],
                'codigo_sscc' => $_POST['codigo_sscc'],
                'cargo' => $_POST['cargo'],
                'nombre_proyecto' => $_POST['nombre_proyecto'],
                'fecha_solicitud' => $_POST['fecha_solicitud'],
                'motivo_anticipo' => $_POST['motivo_anticipo'],
                'monto_total_solicitado' => $_POST['monto-total'],
                'detalles_gastos' => $_POST['detalles_gastos'] ?? [],
                'detalles_viajes' => $_POST['detalles_viajes'] ?? []
            ];

            $result = $this->model->addAnticipo($data);
            if ($result === true) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => $result['error']]);
            }
        } else {
            echo json_encode(['error' => 'Método no permitido']);
        }
    }

    public function update() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_anticipo' => $_POST['edit-id-anticipo'],
                'codigo_sscc' => $_POST['edit-codigo-sscc'],
                'nombre_proyecto' => $_POST['edit-nombre-proyecto'],
                'motivo_anticipo' => $_POST['edit-motivo-anticipo'],
                'monto_total_solicitado' => $_POST['edit-monto-total'],
                'detalles_gastos' => $_POST['edit-detalles_gastos'] ?? [],
                'detalles_viajes' => $_POST['edit-detalles_viajes'] ?? []
            ];

            // Validar saldo disponible
            $saldo_disponible = $this->model->getSaldoDisponibleBySscc($data['codigo_sscc']);
            if ($data['monto_total_solicitado'] > $saldo_disponible) {
                echo json_encode(['error' => 'El monto total solicitado excede el saldo disponible']);
                return;
            }

            $result = $this->model->updateAnticipo($data);
            if ($result === true) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => $result['error']]);
            }
        } else {
            echo json_encode(['error' => 'Método no permitido']);
        }
    }

    // ... (métodos approve, reject, etc.)
}
?>