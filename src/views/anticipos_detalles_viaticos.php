<?php 
$hoja_de_estilos = "anticipoDetallesViajes.css?v=" . time();
$titulo = "Anticipo Detalles";
$fun = "anticipoDetallesViajes.js?v=" . time();
// No incluimos base.php ni footer.php aquí, ya que se inyecta en el modal

// Procesar datos para el informe
$viaticos = ['transporte' => 0, 'movilidad' => 0, 'hospedaje' => 0];
$alimentacion_por_persona = [];
$monto_total = 0;

if (!empty($info_anticipo['anticipo']) && isset($_GET['id_anticipo'])) {
    $anticipo = $info_anticipo['anticipo'];
    $personas = $info_anticipo['personas'];
    $transporte = $info_anticipo['transporte'];
    $detalles = $info_anticipo['detalles'];

    // Sumar viáticos de transporte
    foreach ($transporte as $t) {
        $viaticos['transporte'] += floatval($t['monto_transporte']);
    }

    // Sumar viáticos y alimentación por persona
    foreach ($detalles as $d) {
        $concepto = $d['id_concepto'];
        $monto = floatval($d['monto_detalle']);
        $persona_id = $d['id_viaje_persona'];
        $persona = array_filter($personas, fn($p) => $p['viaje_id'] == $persona_id);
        $persona = reset($persona)['nombre_persona'] ?: $anticipo['solicitante_nombres'];

        if ($concepto == 3) $viaticos['movilidad'] += $monto; // Movilidad
        elseif ($concepto == 2) $viaticos['hospedaje'] += $monto; // Hospedaje
        elseif ($concepto == 1) {
            if (!isset($alimentacion_por_persona[$persona])) {
                $alimentacion_por_persona[$persona] = 0;
            }
            $alimentacion_por_persona[$persona] += $monto;
        }
    }

    $monto_total = $viaticos['transporte'] + $viaticos['movilidad'] + $viaticos['hospedaje'] + array_sum($alimentacion_por_persona);
}
?>

<div class="viaticos-report">
    <p class="title">Informe de Viáticos - Anticipo #<?php echo $anticipo['anticipo_id'] ?? ''; ?></p>
    <p class="solicitante">Solicitante: <?php echo $anticipo['solicitante_nombres'] ?? ''; ?> (DNI: <?php echo $anticipo['dni_solicitante'] ?? ''; ?>)</p>
    <p class="num-cuenta">Número de Cuenta: <?php echo $anticipo['n_cuenta_solicitante'] ?? 'No disponible'; ?></p>

    <p class="resumen">Resumen de Viáticos</p>
    <table>
        <tr><th>Concepto</th><th>Monto</th></tr>
        <tr><td>Transporte Provincial</td><td><?php echo number_format($viaticos['transporte'], 2); ?></td></tr>
        <tr><td>Movilidad</td><td><?php echo number_format($viaticos['movilidad'], 2); ?></td></tr>
        <tr><td>Hospedaje</td><td><?php echo number_format($viaticos['hospedaje'], 2); ?></td></tr>
        <tr><td class="total">Total Viáticos</td><td class="total"><?php echo number_format($viaticos['transporte'] + $viaticos['movilidad'] + $viaticos['hospedaje'], 2); ?></td></tr>
    </table>

    <p class="alimentacion">Alimentación por Persona</p>
    <table>
        <tr><th>Persona</th><th>Monto</th><th>N° de cuenta</th></tr>
        <?php foreach ($alimentacion_por_persona as $persona => $monto): 
            // Buscar la persona en $personas para obtener el n_cuenta
            $persona_data = array_filter($personas, fn($p) => $p['nombre_persona'] == $persona);
            $persona_data = reset($persona_data);
            $n_cuenta = $persona_data ? ($persona_data['n_cuenta'] ?? 'No disponible') : 'No disponible';
        ?>
            <tr>
                <td><?php echo htmlspecialchars($persona); ?></td>
                <td><?php echo number_format($monto, 2); ?></td>
                <td><?php echo $n_cuenta; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p class="total-title">Total Anticipo</p>
    <p class="total">Monto Total: <?php echo number_format($monto_total, 2); ?> PEN</p>
    <div class="botones-detalles-viaticos">
        <div class="btn print-viaticos" id="print-viaticos"><i class="fa-solid fa-download"></i> <span>Descargar</span></div>
    </div>
</div>