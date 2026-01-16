<?php 
$hoja_de_estilos = "anticipoDetallesViajes.css?v=" . time();
$titulo = "Anticipo Detalles";
$fun = "anticipoDetallesViajes.js?v=" . time();

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
   
    <!-- ENCABEZADO EN TABLA A4 -->
    <table class="info-table">
        <tr>
            <th>Fecha Solicitud</th>
            <td><?php echo $anticipo['fecha_solicitud'] ?? 'No disponible'; ?></td>
            <th>Centro Costos</th>
            <td><?php echo $anticipo['codigo_sscc'] ?? 'No disponible'; ?></td>
            <th>DNI</th>
            <td><?php echo $anticipo['dni_solicitante'] ?? 'No disponible'; ?></td>
        </tr>
        <tr>
            <th>Solicitante</th>
            <td colspan="3"><?php echo htmlspecialchars($anticipo['solicitante_nombres'] ?? ''); ?></td>
            <th>N° Cuenta</th>
            <td><?php echo $anticipo['n_cuenta_solicitante'] ?? 'No disponible'; ?></td>
        </tr>
        <tr>
            <th>Proyecto</th>
            <td colspan="5"><?php echo htmlspecialchars($anticipo['nombre_proyecto'] ?? 'No disponible'); ?></td>
        </tr>
        <tr>
            <th>Motivo del Anticipo</th>
            <td colspan="5" class="motivo-cell"><?php echo nl2br(htmlspecialchars($anticipo['motivo_anticipo'] ?? 'No disponible')); ?></td>
        </tr>
        <?php if (!empty($anticipo['fecha_inicio']) && !empty($anticipo['fecha_fin'])): ?>
        <tr>
            <th>Fecha Inicio</th>
            <td><?php echo $anticipo['fecha_inicio']; ?></td>
            <th>Fecha Fin</th>
            <td colspan="3"><?php echo $anticipo['fecha_fin']; ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <h3 class="section-title">Resumen de Viáticos</h3>
    <table class="data-table">
        <thead>
            <tr><th>Concepto</th><th class="amount">Monto (PEN)</th></tr>
        </thead>
        <tbody>
            <tr><td>Transporte Provincial</td><td class="amount"><?php echo number_format($viaticos['transporte'], 2); ?></td></tr>
            <tr><td>Movilidad</td><td class="amount"><?php echo number_format($viaticos['movilidad'], 2); ?></td></tr>
            <tr><td>Hospedaje</td><td class="amount"><?php echo number_format($viaticos['hospedaje'], 2); ?></td></tr>
            <tr class="total-row"><td>Total Viáticos</td><td class="amount"><?php echo number_format($viaticos['transporte'] + $viaticos['movilidad'] + $viaticos['hospedaje'], 2); ?></td></tr>
        </tbody>
    </table>

    <h3 class="section-title">Alimentación por Persona</h3>
    <table class="data-table">
        <thead>
            <tr><th>Persona</th><th class="amount">Monto (PEN)</th><th>N° Cuenta</th></tr>
        </thead>
        <tbody>
            <?php foreach ($alimentacion_por_persona as $persona => $monto):
                $persona_data = array_filter($personas, fn($p) => $p['nombre_persona'] == $persona);
                $persona_data = reset($persona_data);
                $n_cuenta = $persona_data ? ($persona_data['n_cuenta'] ?? 'No disponible') : 'No disponible';
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($persona); ?></td>
                    <td class="amount"><?php echo number_format($monto, 2); ?></td>
                    <td><?php echo $n_cuenta; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-final">
        <strong>Monto Total del Anticipo: <?php echo number_format($monto_total, 2); ?> PEN</strong>
    </div>

    <div class="print-button-container">
        <button class="btn print-viaticos" id="print-viaticos">
            <i class="fa-solid fa-download"></i> Descargar PDF
        </button>
    </div>
</div>