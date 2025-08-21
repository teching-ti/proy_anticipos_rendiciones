<?php
// Cabeceras para exportar como Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=anticipos.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Si tu base de datos usa UTF-8, aseguras compatibilidad
echo "\xEF\xBB\xBF"; // BOM para Excel reconozca tildes y ñ

// Conectar a la base de datos
$conexion = new mysqli("localhost", "usuario", "password", "mi_base");
$conexion->set_charset("utf8");

$resultado = $conexion->query("SELECT fecha, descripcion, monto FROM anticipos");

// Imprimir tabla en HTML (Excel lo interpreta bien)
echo "<table border='1'>";
echo "<tr><th>Fecha</th><th>Descripción</th><th>Monto</th></tr>";

while($fila = $resultado->fetch_assoc()){
    echo "<tr>";
    echo "<td>".$fila['fecha']."</td>";
    echo "<td>".$fila['descripcion']."</td>";
    echo "<td>".$fila['monto']."</td>";
    echo "</tr>";
}
echo "</table>";
?>
