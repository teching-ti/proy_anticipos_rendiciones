<?php
header('Content-Type: application/json');
require_once '../config/Database.php';
require_once '../models/CostCenterModel.php';

$costCenterModel = new CostCenterModel();

if (isset($_GET['codigo'])) {
    $codigo = trim($_GET['codigo']);
    $sscc = $costCenterModel->getSsccByCodigo($codigo);
    
    if ($sscc) {
        echo json_encode($sscc);
    } else {
        echo json_encode(['error' => 'Sub-subcentro de costo no encontrado.']);
    }
} else {
    echo json_encode(['error' => 'Código no proporcionado.']);
}
?>