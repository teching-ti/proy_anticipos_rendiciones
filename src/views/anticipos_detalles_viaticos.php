    <?php 
    $hoja_de_estilos = "anticipoDetallesViajes.css?v=" . time();
    $titulo = "Anticipo Detalles";
    $fun = "anticipoDetallesViajes.js?v=" . time();
    include "base.php";

    // Limpiar mensajes de sesión
    unset($_SESSION['success'], $_SESSION['error']);

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
        <p class="fecha-actual">Fecha: <?php echo date('d/m/Y H:i', strtotime('now')); ?></p>

        <p class="resumen">Resumen de Viáticos</p>
        <table>
            <tr><th>Concepto</th><th>Monto (PEN)</th></tr>
            <tr><td>Transporte Provincial</td><td><?php echo number_format($viaticos['transporte'], 2); ?></td></tr>
            <tr><td>Movilidad</td><td><?php echo number_format($viaticos['movilidad'], 2); ?></td></tr>
            <tr><td>Hospedaje</td><td><?php echo number_format($viaticos['hospedaje'], 2); ?></td></tr>
            <tr><td class="total">Total Viáticos</td><td class="total"><?php echo number_format($viaticos['transporte'] + $viaticos['movilidad'] + $viaticos['hospedaje'], 2); ?></td></tr>
        </table>

        <p class="alimentacion">Alimentación por Persona</p>
        <table>
            <tr><th>Persona</th><th>Monto Alimentación (PEN)</th></tr>
            <?php foreach ($alimentacion_por_persona as $persona => $monto): ?>
                <tr><td><?php echo htmlspecialchars($persona); ?></td><td><?php echo number_format($monto, 2); ?></td></tr>
            <?php endforeach; ?>
        </table>

        <p class="total-title">Total Anticipo</p>
        <p class="total">Monto Total: <?php echo number_format($monto_total, 2); ?> PEN</p>

        <button class="btn print-viaticos" id="print-viaticos"><i class="fa-solid fa-download"></i><span>Descargar</span></button>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Manejar el botón de impresión
        document.getElementById('print-viaticos').addEventListener('click', function() {
            document.getElementById("open-responsive-menu").style.display = "none";
            document.getElementById("user-first-info").style.display = "none";
            document.getElementById("print-viaticos").style.display = "none";
            window.print();
        });
    });
    </script>

    <?php include "footer.php"; ?>