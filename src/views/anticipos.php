<?php 
$hoja_de_estilos = "anticipos.css?v=".time();
$titulo = "Anticipos";
$fun = "anticipos.js?v=".time();
include "base.php";

// Limpiar mensajes de sesión
unset($_SESSION['success'], $_SESSION['error']);
?>

<section class="anticipos-content" style="display: none;">
    <!-- Incluir alert.js -->
    <script src="assets/scripts/modalAlert.js?v=<?=time();?>"></script>

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
                <div id="btn-refresh" class="btn btn-refresh" title="Actualizar interfaz">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </div>
            </div>

            <!-- Botón Flotante -->
            <button id="toggleFilters" class="fab-filter">
                <i id="icono-filtro" class="fa-solid fa-filter"></i>
            </button>
            <div id="tooltip-filtros" class="tooltip-filtros">Mostrar filtros</div>

            <div id="filterPanel" class="filter-panel">
                <form id="filtersForm" class="filtersForm">
                    <h3>Filtros para anticipos</h3>
                    <div class="filter-row">
                        <!-- Filtro Año -->
                        <div>
                            <label for="filtro-anio">Año</label>
                            <select id="filtro-anio">
                                <option value="">Todos</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                            </select>
                        </div>

                        <div>
                            <label for="filtro-estado">Estado</label>
                            <select id="filtro-estado">
                                <option value="">Todos</option>
                                <option value="nuevo">Nuevo</option>
                                <option value="autorizado">Autorizado</option>
                                <option value="autorizado por gerencia">Aut. Gerencia</option>
                                <option value="autorizado totalmente">Aut. Totalmente</option>
                                <option value="abonado">Abonado</option>
                                <option value="observado">Observado</option>
                                <option value="anulado">Anulado</option>
                                <option value="rendido">Rendido</option>
                            </select>
                        </div>
                    </div>
                    <!-- Fecha solicitud -->
                    <div class="filter-box">
                        <p class="title-filter-date">Fecha de solicitud</p>
                        <div class="filter-row">
                            <div>
                                <label>Desde:</label>
                                <input type="date" id="filtro-solicitud-desde">
                            </div>
                            <div>
                                <label>Hasta:</label>
                                <input type="date" id="filtro-solicitud-hasta">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones -->
                    <div class="filter-buttons-container">
                        <button type="submit" id="btn-aplicar" class="btn-aplicar">Aplicar</button>
                        <button id="limpiar-filtros" class="btn-limpiar">Limpiar</button>
                    </div>
                </form>               
            </div>

        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-head">
                    <tr>
                        <th>ID</th>
                        <th id="ordenar-fecha-solicitud" class="sortable">Fecha creación <i id="flecha-fecha-solicitud" class="fa-solid fa-sort ms-1"></i></th>
                        <th>Nombre y apellido</th>
                        <th>Departamento</th>
                        <th>SSCC</th>
                        <th>Motivo&nbsp;del&nbsp;Anticipo</th>
                        <th>Monto</th>
                        <th id="ordenar-estado" class="sortable">Estado <i id="flecha-estado" class="fa-solid fa-sort ms-1"></i></th>
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
                        <p class="step-indicator">
                            <span class="paso-anticipo-actual"><i class="fa-solid fa-id-card-clip"></i> 1. Datos Generales</span><span class="paso-anticipo"><i class="fa-solid fa-cash-register"></i> 2. Datos de compras y/o viáticos</span><span class="paso-anticipo"><i class="fa-solid fa-file-pdf"></i> 3. Documento de autorización</span>
                        </p>
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
                                <select class="form-control anticipo-select" id="codigo_scc" name="codigo_scc" required>
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
                                <select class="form-control anticipo-select" id="codigo_sscc" name="codigo_sscc" required>
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
                            <div class="modal-element">
                                <span class="placeholder">Fecha de Ejecución</span>
                                <input type="date" class="form-control" id="fecha_ejecucion" name="fecha_ejecucion" required>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Fecha de Finalización</span>
                                <input type="date" class="form-control" id="fecha_finalizacion" name="fecha_finalizacion" required>
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
                        <p class="step-indicator">
                            <span class="paso-anticipo"><i class="fa-solid fa-id-card-clip"></i> 1. Datos Generales</span><span class="paso-anticipo-actual"><i class="fa-solid fa-cash-register"></i> 2. Datos de compras y/o viáticos</span><span class="paso-anticipo"><i class="fa-solid fa-file-pdf"></i> 3. Documento de autorización</span>
                        </p>
                        <div class="title-concepto" style="">
                            <h3>2. Concepto</h3>
                            <div class="concepto-categoria">
                                <div class="opcion-concepto">
                                    <input type="radio" name="concepto" id="compras-menores" value="compras-menores" checked>
                                    <label for="compras-menores" class="label-opcion">Compras <i class="fa-solid fa-basket-shopping"></i></label>
                                </div>
                                <div class="opcion-concepto">
                                    <input type="radio" name="concepto" id="viajes" value="viajes">
                                    <label for="viajes" class="label-opcion">Viajes <i class="fa-solid fa-van-shuttle"></i></label>
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
                                    <div class="tab-button add-tab" id="add-tab" title="Agregar Persona">+</div>
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
                            <div class="btn btn-default" id="btn-guardar-continuar">Guardar y continuar <i class="fa-solid fa-caret-right"></i></div>
                        </div>
                    </div>

                    <div class="form-step" id="step-3">
                        <p class="step-indicator">
                            <span class="paso-anticipo"><i class="fa-solid fa-id-card-clip"></i> 1. Datos Generales</span><span class="paso-anticipo"><i class="fa-solid fa-cash-register"></i> 2. Datos de compras y/o viáticos</span><span class="paso-anticipo-actual"><i class="fa-solid fa-file-pdf"></i> 3. Documento de autorización</span>
                        </p>
                        <h3>3. Adjuntar documento de autorización de descuento</h3>
                        <div id="contenedor-creacion-adjuntar-autorizacion">
                            <div id="contenedor-texto-descarga">
                                <div class="btn">
                                    Estimado usuario, por favor descargue el documento de autorzación de descuento y adjúntelo en esta sección
                                </div>
                                <div class="btn btn-doc-aut-download" id="btn-doc-aut-download">
                                    Descargar <i class="fa-solid fa-download"></i>
                                </div>
                            </div>
                            
                            <div id="previewContainer"></div>
                            
                            <div id="contenedor-archivo-seleccion">
                                <!-- Seleccionar archivo -->
                                <label for="archivo" id="lbl-archivo">Adjuntar archivo</label>
                                <input type="file" name="archivo" id="archivo" accept=".pdf, .docx" required style="display: none;">
                                <span id="texto-archivo-seleccionado">Ningún archivo seleccionado</span>
                                <input type="hidden" name="trabajador_id" id="trabajador_id">
                                <input type="hidden" name="doc_name" id="doc_name">
                            </div>
                            
                        </div>
                        <hr>
                        <div class="modal-footer">
                            <div class="btn btn-terminar-anticipo bloq-terminar-anticipo" id="btn-terminar-anticipo">Terminar</div>
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
                                <input type="text" class="form-control" id="edit-motivo-anticipo" name="edit-motivo-anticipo" required readonly>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Fecha de Ejecución</span>
                                <input type="date" class="form-control" id="edit-fecha-ejecucion" name="edit-fecha-ejecucion" required disabled>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Fecha de Finalización</span>
                                <input type="date" class="form-control" id="edit-fecha-finalizacion" name="edit-fecha-finalizacion" required disabled>
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
                        <div class="edit-title-concepto">
                            <h3>2. Concepto</h3>
                            <div class="concepto-categoria">
                                <div class="opcion-concepto">
                                    <input type="radio" name="edit-concepto" id="edit-compras-menores" value="edit-compras-menores" checked>
                                    <label for="edit-compras-menores" class="label-opcion">Compras <i class="fa-solid fa-basket-shopping"></i></label>
                                </div>
                                <div class="opcion-concepto">
                                    <input type="radio" name="edit-concepto" id="edit-viajes" value="edit-viajes">
                                    <label for="edit-viajes" class="label-opcion">Viajes <i class="fa-solid fa-van-shuttle"></i></label>
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
                            <div class="container-doc-autorizacion">
                                <p class="edit-doc-autorizacion-title">Documento de autorización de descuento</p>
                                <div class="edit-doc-autorizacion-elementos">
                                  
                                        <div id="get-doc-autorizacion" title="Descargar el documento de autorización para proceder con el anticipo" class="btn btn-default">Descargar <i class="fa-solid fa-download"></i></div>
                                        
                                        <div id="edit-set-archivo-autorizacion" class="btn btn-aniadir-autorizacion" title="Adjuntar autorización firmada y completa">
                                            Adjuntar <i class="fa-solid fa-file-circle-plus"></i>
                                        </div>
                                        <div id="editFileInfo">
                                            <span id="editfileStatus" style="color: gray;">Sin archivo</span>
                                            <div id="editRemoveFile"><i class="fa-solid fa-xmark fa-lg" style="color: #be3c3c;"></i></div>
                                        </div>
                             
                                    <input type="file" id="edit-archivo-autorizacion" name="edit-archivo-autorizacion" style="display: none;" accept=".pdf,.doc,.docx,.jpg,.png">
                                    <a href="#" id="edit-enlace-archivo"><p></p><i class="fa-solid fa-file-arrow-down"></i></a>
                                </div>
                            </div>                         
                            <div id="container-descarga"></div>
                            <hr>
                            <div id="container-terminar-edicion">
                                <button type="submit" class="btn btn-default" id="btn-terminar-edicion">Terminar</button>
                            </div>
                            <div id="container-cambio-estado">
                                <?php if($_SESSION['rol']==2): ?>
                                    <div class="btn-aprobar-anticipo" title="Se brinda autorización para el anticipo" data-aprobador="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Autorizar
                                    </div>
                                <?php endif;?>
                                <?php if($_SESSION['rol']==2 && $_SESSION['aprob_gerencia']==1): ?>
                                    <div class="btn-aprobar-anticipo-gerencia" title="Se brinda autorización de gerencia para el anticipo" data-aprobador="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Autorizar Grte.
                                    </div>
                                <?php endif; ?>
                                <?php if($_SESSION['rol']==5): ?>
                                    <div class="btn-aprobar-totalmente" data-aprobador="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Autorizar Totalmente
                                    </div>
                                    <div class="btn-observar-anticipo" data-aprobador="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Observar
                                    </div>
                                    <div class="btn-abonar-anticipo" data-aprobador="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Abonado
                                    </div>
                                    <div class="btn-anular-anticipo" data-aprobador="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Anular
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

    <!-- Modal de carga -->
    <div id="loadingModal" class="loading-modal" style="display: none;">
        <div class="loading-content">
            <div class="spinner"></div>
            <p>Cargando...</p>
        </div>
    </div>

</section>

<section>
    <div id="loadingModalPage" class="loading-modal-page" style="display: block;">
        <div class="loading-content-page">
            <div class="spinner"></div>
            <p>Cargando...</p>
        </div>
    </div>
</section>
<!-- Librería para exportar en excel -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>

<?php include "footer.php"; ?>