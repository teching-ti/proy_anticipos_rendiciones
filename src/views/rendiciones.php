<?php 
$hoja_de_estilos = "rendiciones.css?v=".time();
$titulo = "Rendiciones";
$fun = "rendiciones.js?v=".time();
include "base.php";

// Limpiar mensajes de sesión
unset($_SESSION['success'], $_SESSION['error']);
?>
<section class="rendiciones-content">
    <!-- Incluir alert.js -->
    <script src="assets/scripts/modalAlert.js?v=<?=time();?>"></script>

    <!-- Tabla de rendiciones -->
    <section class="section-table">
        <h2>Listado de Rendiciones</h2>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-head">
                    <tr>
                        <th>Id</th>
                        <th>Id. Anticipo</th>
                        <th>Nombre y apellido</th>
                        <th>Departamento</th>
                        <th>SSCC</th>
                        <th title="Fecha de cuando se registró la rendición automáticamente, tras haber registrado el abono">Inicio</th>
                        <th title="Fecha en la que el usuario debería de haber terminado de rendir el anticipo correspondiente. (3 días tras haberse generado el abono)">Rendición estimada</th>
                        <th>Estado</th>
                        <th title="Cambio de estado más reciente">Ult. Actualizacion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rendiciones_data)): ?>
                        <tr><td colspan="<?php echo isset($_SESSION['rol']) && $_SESSION['rol'] == 2 ? 9 : 8; ?>">No hay anticipos registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($rendiciones_data as $rendicion): ?>
                        <tr>
                            <td data-label="Id"><?php echo htmlspecialchars($rendicion['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Id. Anticipo"><?php echo htmlspecialchars($rendicion['id_anticipo'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Nombre y Apellido"><?php echo htmlspecialchars($rendicion['solicitante_nombres'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Departamento"><?php echo htmlspecialchars($rendicion['departamento_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="SSCC" title="<?php echo htmlspecialchars($rendicion['nombre_proyecto'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($rendicion['codigo_sscc'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Inicio"><?php echo htmlspecialchars($rendicion['fecha_inicio'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Rendicion estimada"><?php echo htmlspecialchars($rendicion['fecha_rendicion'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td data-label="Estado" class="td-estado">
                                <span class="span-td-estado <?=strtolower($rendicion['estado']);?>" title="<?=strtolower($rendicion['estado']);?>"><?php echo htmlspecialchars($rendicion['estado'], ENT_QUOTES, 'UTF-8'); ?>
                            </span></td>
                            <td data-label="Ult. Actualizacion"><?php echo htmlspecialchars($rendicion['historial_fecha'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Modal para cargar datos de la rendicion -->
    <div id="completarRendicionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="rendicion-modal-title" id="rendicion-modal-title"></h2>
                <div class="btn-close-modal" data-modal="completarRendicionModal"><i class="fa-solid fa-lg fa-xmark"></i></div>
            </div>
            <form id="completarRendicionForm">
                <div class="modal-body">
                    <div class="form-step active" id="step-1">
                        <h3>1. Datos Principales</h3>
                        <div class="datos-responsable-container">
                            <div class="modal-codigos">
                                <div class="modal-element">
                                    <span class="input-icon-left">
                                        <i class="fa-solid fa-money-check"></i>
                                    </span>
                                    <span class="placeholder">Id. Rendicion</span>
                                    <input type="text" class="form-control-icon w-cod" id="id-rendicion" name="id-rendicion" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="input-icon-left">
                                        <i class="fa-solid fa-hand-holding-dollar"></i>
                                    </span>
                                    <span class="placeholder">Id. Anticipo</span>
                                    <input type="text" class="form-control-icon w-cod" id="id-anticipo" name="id-anticipo" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="input-icon-left">
                                        <i class="fa-solid fa-coins"></i>
                                    </span>
                                    <span class="placeholder">Cod. SSCC</span>
                                    <input type="text" class="form-control-icon w-cod" id="cod-sscc" name="cod-sscc" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="input-icon-left">
                                        <i class="fa-solid fa-coins"></i>
                                    </span>
                                    <span class="placeholder">Cod. SCC</span>
                                    <input type="text" class="form-control-icon w-cod" id="cod-scc" name="cod-scc" readonly>
                                </div>

                            </div>
                            <div class="modal-nombre-motivo">
                                <div class="modal-element">
                                    <span class="input-icon-left">
                                        <i class="fa-solid fa-diagram-project"></i>
                                    </span>
                                    <span class="placeholder">Nombre del Proyecto</span>
                                    <input type="text" class="form-control-icon" id="nombre-proyecto" name="nombre-proyecto" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="input-icon-left">
                                        <i class="fa-solid fa-cube"></i>
                                    </span>
                                    <span class="placeholder">Motivo</span>
                                    <input type="text" class="form-control-icon" id="motivo-anticipo" name="motivo-anticipo" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="datos-usuario-container">
                                <div class="modal-element">
                                    <span class="input-icon-left">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </span>
                                    <span class="placeholder">Responsable</span>
                                    <input type="text" class="form-control-icon" id="rendicion-responsable" name="rendicion-responsable" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="input-icon-left">
                                        <i class="fa-solid fa-id-card"></i>
                                    </span>
                                    <span class="placeholder">Documento Identidad</span>
                                    <input type="text" class="form-control-icon" id="dni-responsable" name="dni-responsable" readonly>
                                </div>

   
                                <div class="modal-element">
                                    <span class="input-icon-left">
                                        <i class="fa-solid fa-tag"></i>
                                    </span>
                                    <span class="placeholder">Departamento</span>
                                    <input type="text" class="form-control-icon" id="departamento-responsable" name="departamento-responsable"  data-departamento="" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="input-icon-left">
                                        <i class="fa-solid fa-briefcase"></i>
                                    </span>
                                    <span class="placeholder">Cargo</span>
                                    <input type="text" class="form-control-icon" id="cargo-responsable" name="cargo-responsable" readonly>
                            </div>
                        </div>
                        <hr>
                        <div class="modal-footer">
                            <div class="btn btn-default" onclick="nextStep()">Siguiente</div>
                        </div>
                    </div>

                    <div class="form-step" id="step-2">
                        <h3>2. Detalles a rendir</h3>
                        <div class="datos-completar-rendicion">
                            <div id="detalles-compras-container">
                                <div class="container-detalle">
                                    <div class="compras-elementos-uno">
                                        <div class="modal-element">
                                            <span class="placeholder">Descripcion</span>
                                            <input type="text" class="rendicion-element" required>
                                        </div>
                                        <div class="modal-element">
                                            <span class="placeholder">Motivo</span>
                                            <input type="text" class="rendicion-element" required>
                                        </div>

                                        <div class="modal-element">
                                            <span class="placeholder">Monto Solicitado</span>
                                            <input type="text" class="rendicion-element" required>
                                        </div>
                                        <div class="modal-element">
                                            <span class="placeholder">Monto Rendido</span>
                                            <input type="text" class="rendicion-element" required>
                                        </div>
                                    </div>
                                    <div class="compras-elementos-dos">
                                        <div class="modal-element">
                                            <span class="placeholder">Fecha</span>
                                            <input type="date" class="rendicion-element" required>
                                        </div>
                                        <div class="modal-element">
                                            <div class="btn btn-adjuntar">Adjuntar</div>
                                        </div>
                                        <p class="enlace-factura">
                                            <a href="#">Factura...</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="modal-footer">
                            <div class="btn btn-default" onclick="prevStep()">Atrás</div>
                            <button type="submit" class="btn btn-default" id="btn-guardar-rendicion">Guardar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</section>
<?php include "footer.php"; ?>