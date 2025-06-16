<?php
header('Content-Type: application/json');
require_once '../config/Database.php';
require_once '../models/CostCenterModel.php';

$costCenterModel = new CostCenterModel();

if (isset($_GET['codigo'])) {
    $codigo = trim($_GET['codigo']);
    $scc = $costCenterModel->getSccByCodigo($codigo);
    
    if ($scc) {
        echo json_encode($scc);
    } else {
        echo json_encode(['error' => 'Subcentro de costo no encontrado.']);
    }
} else {
    echo json_encode(['error' => 'Código no proporcionado.']);
}
?>