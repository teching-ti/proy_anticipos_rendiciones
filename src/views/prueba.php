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
                    <input class="form-control" type="hidden" id="edit-id-anticipo" name="edit-id-anticipo" readonly>
                    <input class="form-control" type="hidden" id="edit-estado-anticipo" name="edit-estado-anticipo" readonly disabled>
                    <div class="datos-solicitantes-container">
                        <div class="modal-element">
                            <span class="input-icon-left"><i class="fa-solid fa-user-tie"></i></span>
                            <span class="placeholder">Solicitante</span>
                            <input type="text" class="form-control" id="edit-solicitante" name="edit-solicitante" readonly>
                        </div>
                        <div class="modal-element">
                            <span class="input-icon-left"><i class="fa-solid fa-id-card"></i></span>
                            <span class="placeholder">Documento Identidad</span>
                            <input type="text" class="form-contro" id="edit-dni-solicitante" name="edit-dni-solicitante" readonly>
                        </div>
                        <div class="modal-element">
                            <span class="input-icon-left"><i class="fa-solid fa-tag"></i></span>
                            <span class="placeholder">Departamento</span>
                            <input type="text" class="form-control" id="edit-departamento" name="edit-departamento" readonly>
                        </div>
                        <div class="modal-element">
                            <span class="input-icon-left"><i class="fa-solid fa-briefcase"></i></span>
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
                        <div class="viaticos-detalles" title="Detalles de viáticos"><i class="fa-solid fa-circle-info fa-lg"></i></div>
                    </div>
                    <!-- Sección de Detalles de Viáticos -->
                    <div class="viaticos-details-section" id="viaticos-details-section" style="display: none;">
                        <h3>Detalles de Viáticos</h3>
                        <div class="viaticos-summary">
                            <h4>Resumen de Viáticos</h4>
                            <table>
                                <tr><th>Concepto</th><th>Monto (PEN)</th></tr>
                                <tr><td>Transporte Provincial</td><td id="transporte-total">0.00</td></tr>
                                <tr><td>Movilidad</td><td id="movilidad-total">0.00</td></tr>
                                <tr><td>Hospedaje</td><td id="hospedaje-total">0.00</td></tr>
                                <tr><td><b>Total Viáticos</b></td><td id="viaticos-total">0.00</td></tr>
                            </table>
                        </div>
                        <div class="alimentacion-summary">
                            <h4>Alimentación por Persona</h4>
                            <table id="alimentacion-table">
                                <tr><th>Persona</th><th>Monto (PEN)</th></tr>
                            </table>
                        </div>
                        <div class="total-anticipo">
                            <h4>Total Anticipo</h4>
                            <p><b>Monto Total: <span id="total-anticipo">0.00</span> PEN</b></p>
                        </div>
                        <button class="btn print-viaticos" id="print-viaticos">Imprimir</button>
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
            </form>
        </div>
    </div>