<?php 
$hoja_de_estilos = "anticipos.css?v=".time();
$titulo = "Anticipos";
$fun = "anticipos.js?v=".time();
include "base.php";

// Limpiar mensajes de sesión
unset($_SESSION['success'], $_SESSION['error']);
?>

<section class="anticipos-content">
    <!-- Incluir alert.js -->
    <script src="assets/scripts/modalAlert.js"></script>

    <!-- Notificaciones -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            showAlert({
                title: 'Éxito',
                message: '<?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); ?>',
                type: 'success'
            });
        </script>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <script>
            showAlert({
                title: 'Error',
                message: '<?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>',
                type: 'error'
            });
        </script>
    <?php endif; ?>

    <!-- Tabla de anticipos -->
    <section class="section-table">
        <h2>Listado de Anticipos</h2>
        <div class="help-panel">
            <div class="container-search-input">
                <span class="placeholder">Buscar</span>
                <input type="text" class="form-control" id="input-buscar-anticipo" name="input-buscar-anticipo">
            </div>
            <div class="help-panel-buttons">
                <div class="btn btn-add-anticipo"><i class="fa-solid fa-circle-plus fa-lg"></i> Agregar Anticipo</div>
                <div id="btn-refresh" class="btn btn-refresh">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-head">
                    <tr>
                        <th>ID</th>
                        <th>Fecha creación</th>
                        <th>Nombre y apellido</th>
                        <th>Departamento</th>
                        <th>SSCC</th>
                        <th>Motivo&nbsp;del&nbsp;Anticipo</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th title="Cambio de estado más reciente">Ult. Actualización</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <?php if (empty($anticipos_data)): ?>
                        <tr><td colspan="<?php echo isset($_SESSION['rol']) && $_SESSION['rol'] == 2 ? 9 : 8; ?>">No hay anticipos registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($anticipos_data as $anticipo): ?>
                            <tr>
                                <td data-label="ID"><?php echo htmlspecialchars($anticipo['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Fecha Solicitud"><?php echo htmlspecialchars($anticipo['fecha_solicitud'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Solicitante"><?php echo htmlspecialchars($anticipo['solicitante_nombres'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Departamento"><?php echo htmlspecialchars($anticipo['departamento_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="SSCC"><?php echo htmlspecialchars($anticipo['codigo_sscc'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Motivo del Anticipo" title="<?php echo htmlspecialchars($anticipo['motivo_anticipo'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($anticipo['motivo_anticipo'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Monto"><?php echo number_format($anticipo['monto_total_solicitado'], 2); ?></td>
                                <td data-label="Estado" class="td-estado">
                                    <span class="span-td-estado <?=strtolower($anticipo['estado']);?>" title="<?=ucfirst($anticipo['comentario']);?>"><?php echo htmlspecialchars($anticipo['estado'], ENT_QUOTES, 'UTF-8'); ?>
                                </span></td>
                                <td data-label="Ult. Actualizacion"><?php echo htmlspecialchars($anticipo['historial_fecha'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Modal para Agregar Anticipo -->
    <div id="addAnticipoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Creando anticipo</h3>
                <div class="btn-close-modal" data-modal="addAnticipoModal"><i class="fa-solid fa-lg fa-xmark"></i></div>
            </div>
            <form id="addAnticipoForm" >
                <div class="modal-body">
                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id_cat_documento" value="1">
                    <!-- 1. Datos del solicitante -->
                    <div class="form-step active" id="step-1">

                        <h3>1. Datos del solicitante</h3>
                        
                        <div class="datos-solicitantes-container">
                            <div class="modal-element">
                                <span class="input-icon-left">
                                    <i class="fa-solid fa-user-tie"></i>
                                </span>
                                <span class="placeholder">Solicitante</span>
                                <input type="text" class="form-control" id="solicitante" name="solicitante" value="<?php echo htmlspecialchars($_SESSION['trabajador']['nombres'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($_SESSION['trabajador']['apellidos'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            <div class="modal-element">
                                <span class="input-icon-left">
                                    <i class="fa-solid fa-id-card"></i>
                                </span>
                                <span class="placeholder">Documento Identidad</span>
                                <input type="text" class="form-contro" id="dni_solicitante" name="dni_solicitante" value="<?php echo htmlspecialchars($_SESSION['dni'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            <div class="modal-element">
                                <span class="input-icon-left">
                                    <i class="fa-solid fa-tag"></i>
                                </span>
                                <span class="placeholder">Departamento</span>
                                <input type="text" class="form-control" id="departamento" name="departamento" value="<?php echo htmlspecialchars($_SESSION['trabajador']['departamento_nombre'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                            <div class="modal-element">
                                <span class="input-icon-left">
                                    <i class="fa-solid fa-briefcase"></i>
                                </span>
                                <span class="placeholder">Cargo</span>
                                <input type="text" class="form-control" id="cargo" name="cargo" value="<?php echo htmlspecialchars($_SESSION['trabajador']['cargo'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="datos-solicitantes-container">
                            <div class="modal-element">
                                <span class="placeholder">Sub centro de costo</span>
                                <select class="form-control" id="codigo_scc" name="codigo_scc" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($sccs as $scc): ?>
                                        <option value="<?php echo htmlspecialchars($scc['codigo'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($scc['codigo'].' - '. $scc['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Sub-subcentro de Costo</span>
                                <select class="form-control" id="codigo_sscc" name="codigo_sscc" required>
                                    <option value="">Seleccione</option>
                                    
                                </select>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Nombre del Proyecto</span>
                                <input type="text" class="form-control" id="nombre_proyecto" name="nombre_proyecto" readonly required>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Motivo del Anticipo</span>
                                <input type="text" class="form-control" id="motivo-anticipo" name="motivo-anticipo" required>
                            </div>
                        </div>

                        <div class="datos-solicitantes-container">
                            <div class="modal-element">
                                <span class="placeholder">Fecha de Solicitud</span>
                                <input type="date" class="form-control" id="fecha_solicitud" name="fecha_solicitud" required readonly>
                            </div>
                        </div>
                        <hr>
                        <div class="modal-footer">
                            <div class="btn btn-default" onclick="nextStep()">Siguiente <i class="fa-solid fa-caret-right"></i></div>
                        </div>
                    </div>

                    <!-- 2. Concepto -->
                    <div class="form-step" id="step-2">
                        <div class="title-concepto" style="">
                            <h3>2. Concepto</h3>
                            <div class="concepto-categoria">
                                <div>
                                    <input type="radio" name="concepto" id="compras-menores" value="compras-menores" checked>
                                    <label for="compras-menores">Compras</label>
                                </div>
                                <div>
                                    <input type="radio" name="concepto" id="viajes" value="viajes">
                                    <label for="viajes">Viajes</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="panel-compras-menores" id="panel-compras-menores">
                            <p class="indicacion-compras"><span>*</span>Las compras no deberán superar los S/. 400, a excepción de combustibles.</p>
                            <div  id="add-gasto-btn" class="btn">Añadir</div>
                        </div>
                        
                        <div class="panel-viajes" id="panel-viajes">
                            <div id="viajes-tabs">
                                <div class="tabs-header" id="tabs-header">
                                    <div class="tab-button add-tab" id="add-tab">+</div>
                                </div>
                                <div class="tabs-body" id="tabs-body">
                                    <!--here -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-element">
                            <span class="input-icon-left"><i class="fa-solid fa-money-bill-wave"></i></span>
                            <span class="placeholder">Monto Total</span>
                            <input type="number" class="form-control" id="monto-total" name="monto-total" required readonly>
                        </div>

                        <hr>
                        <div class="modal-footer">
                            <div class="btn btn-default" onclick="prevStep()"><i class="fa-solid fa-caret-left"></i> Atrás</div>
                            <button type="submit" class="btn btn-default">Terminar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Editar Anticipo -->
    <div id="editAnticipoModal" class="modal" data-user-anticipo="">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="edit-modal-title" id="edit-modal-title"></h3>
                <div class="btn-close-modal" data-modal="editAnticipoModal"><i class="fa-solid fa-lg fa-xmark"></i></div>
            </div>
            <form>
                <div class="btn-container">
                    <label class="switch btn-color-mode-switch">
                        <input value="1" id="color_mode" name="color_mode" type="checkbox">
                        <label class="btn-color-mode-switch-inner" data-off="Ver" data-on="Editar" for="color_mode"></label>
                    </label>
                </div>
                <div class="modal-body">
                    <div class="form-step active" id="edit-step-1">
                        <h3>1. Datos del solicitante</h3>
                        <input class="form-control" type="hidden" id="edit-id-anticipo" name="edit-id-anticipo" readonly> <!--Id del anticipo-->
                        <input class="form-control" type="hidden" id="edit-estado-anticipo" name="edit-estado-anticipo" readonly disabled> <!--Estado del anticipo-->
                        <div class="datos-solicitantes-container">
                            <div class="modal-element">
                                <span class="input-icon-left">
                                    <i class="fa-solid fa-user-tie"></i>
                                </span>
                                <span class="placeholder">Solicitante</span>
                                <input type="text" class="form-control" id="edit-solicitante" name="edit-solicitante" readonly>
                            </div>
                            <div class="modal-element">
                                <span class="input-icon-left">
                                    <i class="fa-solid fa-id-card"></i>
                                </span>
                                <span class="placeholder">Documento Identidad</span>
                                <input type="text" class="form-contro" id="edit-dni-solicitante" name="edit-dni-solicitante" readonly>
                            </div>
                            <div class="modal-element">
                                <span class="input-icon-left">
                                    <i class="fa-solid fa-tag"></i>
                                </span>
                                <span class="placeholder">Departamento</span>
                                <input type="text" class="form-control" id="edit-departamento" name="edit-departamento" readonly>
                            </div>
                            <div class="modal-element">
                                <span class="input-icon-left">
                                    <i class="fa-solid fa-briefcase"></i>
                                </span>
                                <span class="placeholder">Cargo</span>
                                <input type="text" class="form-control" id="edit-cargo" name="edit-cargo" readonly>
                            </div>
                        </div>

                        <div class="datos-solicitantes-container">
                            <div class="modal-element">
                                <span class="placeholder">Sub centro de costo</span>
                                <select class="form-control" id="edit-codigo-scc" name="edit-codigo-scc" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($sccs as $scc): ?>
                                        <option value="<?php echo htmlspecialchars($scc['codigo'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($scc['codigo'].' - '. $scc['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Sub-subcentro de Costo</span>
                                <select class="form-control" id="edit-codigo-sscc" name="edit-codigo-sscc" required>
                                    <option value="">Seleccione</option>
                                    
                                </select>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Nombre del Proyecto</span>
                                <input type="text" class="form-control" id="edit-nombre-proyecto" name="edit-nombre-proyecto" readonly required>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Motivo del Anticipo</span>
                                <input type="text" class="form-control" id="edit-motivo-anticipo" name="edit-motivo-anticipo" required>
                            </div>
                        </div>

                        <div class="datos-solicitantes-container">
                            <div class="modal-element">
                                <span class="placeholder">Fecha de Solicitud</span>
                                <input type="date" class="form-control" id="edit-fecha-solicitud" name="edit-fecha-solicitud" required readonly>
                            </div>
                        </div>
                        <hr>
                    </div>

                    <!-- 2. Concepto -->
                    <div class="edit-form-step" id="edit-step-2">
                        <div class="title-concepto">
                            <h3>2. Concepto</h3>
                            <div class="concepto-categoria">
                                <div>
                                    <input type="radio" name="edit-concepto" id="edit-compras-menores" value="edit-compras-menores" checked>
                                    <label for="edit-compras-menores">Compras</label>
                                </div>
                                <div>
                                    <input type="radio" name="edit-concepto" id="edit-viajes" value="edit-viajes">
                                    <label for="edit-viajes">Viajes</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="panel-compras-menores" id="edit-panel-compras-menores">
                            <p class="indicacion-compras"><span>*</span>Las compras no deberán superar los S/. 400, a excepción de combustibles.</p>
                            <div id="edit-add-gasto-btn" class="btn">Añadir</div>
                        </div>
                        
                        <div class="panel-viajes" id="edit-panel-viajes"> 
                            <div id="edit-viajes-tabs">
                                <div class="tabs-header" id="edit-tabs-header">
                                    <div class="tab-button add-tab" id="add-tab">+</div>
                                </div>
                                <div class="tabs-body" id="edit-tabs-body">
                                    <!--here -->
                                </div>
                            </div>
                        </div>
                        <div class="mount-content">
                            <div class="mount-element">
                                <span class="input-icon-left"><i class="fa-solid fa-money-bill-wave"></i></span>
                                <span class="placeholder">Monto Total</span>
                                <input type="number" class="form-control" id="edit-monto-total" name="edit-monto-total" required readonly>
                            </div>
                            <!-- Boton para mostrar detalles de viaticos -->
                            <?php if($_SESSION['rol']==4 || $_SESSION['rol']==5): ?>
                                <div class="viaticos-detalles" title="Detalles de viáticos"><i class="fa-solid fa-circle-info fa-lg"></i> Detalles viáticos</div>
                            <?php endif;?>
                        </div>
                        <hr>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-default">Terminar</button>
                            <div id="container-cambio-estado">
                                <?php if($_SESSION['rol']==2): ?>
                                    <div class="btn-aprobar-anticipo" data-aprobador="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Autorizado
                                    </div>
                                <?php endif;?>
                                <?php if($_SESSION['rol']==5): ?>
                                    <div class="btn-aprobar-totalmente" data-aprobador="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Autorizado Totalmente
                                    </div>
                                    <div class="btn-observar-anticipo" data-aprobador="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Observado
                                    </div>
                                    <div class="btn-abonar-anticipo" data-aprobador="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Abonado
                                    </div>
                                <?php endif;?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para Detalles de Viáticos -->
    <div id="detalleViaticosModal" class="modal-detalles-viaticos" style="display: none;">
        <div class="modal-content-detalles-viaticos viaticos-report-modal">
            <div class="modal-header-detalles-viaticos">
                <div class="btn-close-modal" id="btn-close-detalles-viaticos" data-modal="detalleViaticosModal"><i class="fa-solid fa-lg fa-xmark"></i></div>
            </div>
            <div class="modal-body" id="detalle-viaticos-content">
                <!-- El contenido se cargará dinámicamente aquí -->
            </div>
        </div>
    </div>

</section>
<?php include "footer.php"; ?>