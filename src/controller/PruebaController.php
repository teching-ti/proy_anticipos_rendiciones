<?php
require_once __DIR__ . '/../../src/model/AnticipoModel.php';

class AnticipoController {
    private $anticipoModel;

    public function __construct($db) {
        $this->anticipoModel = new AnticipoModel($db);
    }

    public function getAllScc() {
        header('Content-Type: application/json');
        try {
            $sccs = $this->anticipoModel->getAllScc();
            echo json_encode($sccs);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getSsccByScc() {
        header('Content-Type: application/json');
        $codigo_scc = $_GET['codigo_scc'] ?? '';
        if (!$codigo_scc) {
            echo json_encode(['error' => 'Código SCC requerido']);
            return;
        }
        try {
            $ssccs = $this->anticipoModel->getSsccByScc($codigo_scc);
            echo json_encode($ssccs);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getSaldoDisponibleTiempoReal() {
        header('Content-Type: application/json');
        $codigo_sscc = $_GET['codigo_sscc'] ?? '';
        if (!$codigo_sscc) {
            echo json_encode(['error' => 'Código SSCC requerido']);
            return;
        }
        try {
            $saldo = $this->anticipoModel->getSaldoDisponibleBySscc($codigo_sscc);
            echo json_encode($saldo);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getAnticipoDetails() {
        header('Content-Type: application/json');
        $id_anticipo = $_GET['id_anticipo'] ?? '';
        if (!$id_anticipo) {
            echo json_encode(['error' => 'ID de anticipo requerido']);
            return;
        }
        try {
            $anticipo = $this->anticipoModel->getAnticipoById($id_anticipo);
            if ($anticipo) {
                echo json_encode($anticipo);
            } else {
                echo json_encode(['error' => 'Anticipo no encontrado']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function update() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_anticipo' => $_POST['edit-id-anticipo'] ?? null,
                'codigo_sscc' => $_POST['edit-codigo-sscc'] ?? null,
                'nombre_proyecto' => $_POST['edit-nombre-proyecto'] ?? null,
                'motivo_anticipo' => $_POST['edit-motivo-anticipo'] ?? null,
                'monto_total_solicitado' => $_POST['edit-monto-total'] ?? 0,
                'detalles_gastos' => [],
                'detalles_viajes' => []
            ];

            // Procesar detalles_gastos
            foreach ($_POST as $key => $value) {
                if (preg_match('/^edit-detalles_gastos\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $field = $matches[2];
                    $data['detalles_gastos'][$index][$field] = $value;
                    // Asegurar que el campo 'valido' esté presente
                    if (!isset($data['detalles_gastos'][$index]['valido'])) {
                        $data['detalles_gastos'][$index]['valido'] = '1';
                    }
                }
            }

            // Procesar detalles_viajes
            foreach ($_POST as $key => $value) {
                if (preg_match('/^edit-detalles_viajes\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $field = $matches[2];
                    $data['detalles_viajes'][$index][$field] = $value;
                    // Asegurar que el campo 'valido' esté presente
                    if (!isset($data['detalles_viajes'][$index]['valido'])) {
                        $data['detalles_viajes'][$index]['valido'] = '1';
                    }
                } elseif (preg_match('/^edit-detalles_viajes\[(\d+)\]\[transporte\]\[(\d+)\]\[(\w+)\]$/', $key, $matches)) {
                    $index = $matches[1];
                    $transporteIndex = $matches[2];
                    $field = $matches[3];
                    $data['detalles_viajes'][$index]['transporte'][$transporteIndex][$field] = $value;
                    // Asegurar que el campo 'valido' esté presente
                    if (!isset($data['detalles_viajes'][$index]['transporte'][$transporteIndex]['valido'])) {
                        $data['detalles_viajes'][$index]['transporte'][$transporteIndex]['valido'] = '1';
                    }
                } elseif (preg_match('/^edit-dias-(hospedaje|movilidad|alimentacion)-(\d+)$/', $key, $matches)) {
                    $concepto = $matches[1];
                    $index = $matches[2] - 1;
                    $data['detalles_viajes'][$index]['viaticos'][strtolower($concepto)]['dias'] = $value;
                } elseif (preg_match('/^edit-monto-(hospedaje|movilidad|alimentacion)-(\d+)$/', $key, $matches)) {
                    $concepto = $matches[1];
                    $index = $matches[2] - 1;
                    $data['detalles_viajes'][$index]['viaticos'][strtolower($concepto)]['monto'] = $value;
                } elseif (preg_match('/^edit-tipo-transporte-(\d+)-(\d+)$/', $key, $matches)) {
                    $index = $matches[1] - 1;
                    $transporteIndex = $matches[2];
                    $data['detalles_viajes'][$index]['transporte'][$transporteIndex]['tipo_transporte'] = $value;
                }
            }

            error_log("Datos recibidos en update: " . print_r($data, true));

            if (!$data['id_anticipo'] || !$data['codigo_sscc'] || !$data['nombre_proyecto'] || !$data['motivo_anticipo']) {
                echo json_encode(['error' => 'Faltan datos requeridos']);
                return;
            }

            if (!is_numeric($data['monto_total_solicitado']) || $data['monto_total_solicitado'] <= 0) {
                echo json_encode(['error' => 'Monto total inválido']);
                return;
            }

            $saldo_disponible = $this->anticipoModel->getSaldoDisponibleBySscc($data['codigo_sscc']);
            if ($data['monto_total_solicitado'] > $saldo_disponible) {
                echo json_encode(['error' => 'El monto total solicitado excede el saldo disponible']);
                return;
            }

            foreach ($data['detalles_gastos'] as $index => $gasto) {
                if ($gasto['valido'] === '1') {
                    if (empty($gasto['descripcion']) || empty($gasto['motivo']) || empty($gasto['moneda']) || !isset($gasto['importe']) || $gasto['importe'] < 0) {
                        echo json_encode(['error' => "Datos incompletos o inválidos en detalles_gastos[$index]"]);
                        return;
                    }
                    if (!in_array($gasto['moneda'], ['PEN', 'USD'])) {
                        echo json_encode(['error' => "Moneda inválida en detalles_gastos[$index]"]);
                        return;
                    }
                    if ($gasto['descripcion'] !== 'Combustible' && $gasto['importe'] > 400) {
                        echo json_encode(['error' => "El importe no puede exceder 400 para el tipo de gasto en detalles_gastos[$index]"]);
                        return;
                    }
                }
            }

            foreach ($data['detalles_viajes'] as $index => $viaje) {
                if ($viaje['valido'] === '1') {
                    if (empty($viaje['doc_identidad']) || empty($viaje['nombre_persona']) || empty($viaje['id_cargo'])) {
                        echo json_encode(['error' => "Datos incompletos en detalles_viajes[$index]"]);
                        return;
                    }
                    foreach ($viaje['transporte'] as $tIndex => $transporte) {
                        if ($transporte['valido'] === '1') {
                            if (empty($transporte['tipo_transporte']) || empty($transporte['ciudad_origen']) || empty($transporte['ciudad_destino']) || empty($transporte['fecha']) || empty($transporte['monto']) || empty($transporte['moneda'])) {
                                echo json_encode(['error' => "Datos incompletos en detalles_viajes[$index][transporte][$tIndex]"]);
                                return;
                            }
                        }
                    }
                    foreach ($viaje['viaticos'] as $concepto => $viatico) {
                        if (!isset($viatico['dias']) || !isset($viatico['monto']) || $viatico['dias'] < 0 || $viatico['monto'] < 0) {
                            echo json_encode(['error' => "Datos incompletos o inválidos en detalles_viajes[$index][viaticos][$concepto]"]);
                            return;
                        }
                    }
                }
            }

            $result = $this->anticipoModel->updateAnticipo($data);
            if ($result === true) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => $result['error']]);
            }
        } else {
            echo json_encode(['error' => 'Método no permitido']);
        }
    }
}
?>