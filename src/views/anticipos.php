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
    <script src="/proy_anticipos_rendiciones/assets/scripts/modalAlert.js"></script>

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
        <button type="button" class="btn btn-primary btn-add-anticipo"><i class="fa-solid fa-circle-plus fa-lg"></i> Agregar Anticipo</button>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-head">
                    <tr>
                        <th>ID</th>
                        <th>Área</th>
                        <th>Sub-subcentro</th>
                        <th>Solicitante</th>
                        <th>Fecha Solicitud</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Act. Usuario</th>
                        <th>Mov. Reciente</th>
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 2): ?>
                            <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($anticipos_data)): ?>
                        <tr><td colspan="<?php echo isset($_SESSION['rol']) && $_SESSION['rol'] == 2 ? 9 : 8; ?>">No hay anticipos registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($anticipos_data as $anticipo): ?>
                            <tr>
                                <td data-label="ID"><?php echo htmlspecialchars($anticipo['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Área"><?php echo htmlspecialchars($anticipo['area'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Sub-subcentro"><?php echo htmlspecialchars($anticipo['codigo_sscc'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Solicitante"><?php echo htmlspecialchars($anticipo['solicitante'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Fecha Solicitud"><?php echo htmlspecialchars($anticipo['fecha_solicitud'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Monto"><?php echo number_format($anticipo['monto_total_solicitado'], 2); ?></td>
                                <td data-label="Estado"><?php echo htmlspecialchars($anticipo['estado'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Act. Usuario"><?php echo htmlspecialchars($anticipo['historial_usuario_nombre'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Mov. Reciente"><?php echo htmlspecialchars($anticipo['historial_fecha'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 2): ?>
                                    <td data-label="Acciones">
                                        <?php if ($anticipo['estado'] == 'Nuevo' || $anticipo['estado'] == 'Pendiente'): ?>
                                            <form action="/proy_anticipos_rendiciones/anticipos/approve" method="POST" style="display: inline;">
                                                <input type="hidden" name="id" value="<?php echo $anticipo['id']; ?>">
                                                <input type="text" name="comentario" placeholder="Comentario (opcional)" class="form-control d-inline-block" style="width: 150px; margin-right: 5px;">
                                                <button type="submit" class="btn btn-success btn-sm">Aprobar</button>
                                            </form>
                                            <form action="/proy_anticipos_rendiciones/anticipos/reject" method="POST" style="display: inline;">
                                                <input type="hidden" name="id" value="<?php echo $anticipo['id']; ?>">
                                                <input type="text" name="comentario" placeholder="Comentario (opcional)" class="form-control d-inline-block" style="width: 150px; margin-right: 5px;">
                                                <button type="submit" class="btn btn-danger btn-sm">Rechazar</button>
                                            </form>
                                        <?php else: ?>
                                            <span><?php echo htmlspecialchars($anticipo['estado'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
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
                <h2>Creando anticipo</h2>
                <button type="button" class="btn-close-modal" data-modal="addAnticipoModal"><i class="fa-solid fa-lg fa-xmark"></i></button>
            </div>
            <form action="/proy_anticipos_rendiciones/anticipos/add" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id_cat_documento" value="<?php echo htmlspecialchars($id_cat_documento, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="modal-elements-container">
                        <div class="modal-element">
                            <!-- <label for="solicitante">Solicitante</label> -->
                            <span class="placeholder">Solicitante</span>
                            <input type="text" class="form-control" id="solicitante" name="solicitante" value="<?php echo htmlspecialchars($_SESSION['trabajador']['nombres'], ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($_SESSION['trabajador']['apellidos'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                        </div>
                        <div class="modal-element">
                            <span class="placeholder">Documento Identidad</span>
                            <input type="text" class="form-control" id="dni_solicitante" name="dni_solicitante" value="<?php echo htmlspecialchars($_SESSION['dni'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                        </div>
                        <div class="modal-element">
                            <span class="placeholder">Departamento</span>
                            <input type="text" class="form-control" id="area" name="area" value="<?php echo htmlspecialchars($_SESSION['trabajador']['departamento'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                        </div>
                        <div class="modal-element">
                            <span class="placeholder">Cargo</span>
                            <input type="text" class="form-control" id="cargo" name="cargo" value="<?php echo htmlspecialchars($_SESSION['trabajador']['cargo'], ENT_QUOTES, 'UTF-8'); ?>" readonly>
                        </div>
                    </div>
                    
                    <div class="modal-elements-container">
                        <div class="modal-element">
                            <span class="placeholder">Sub centro de costo</span>
                            <select class="form-control" id="codigo_scc" name="codigo_scc" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($sccs as $scc): ?>
                                    <option value="<?php echo htmlspecialchars($scc['codigo'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($scc['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="modal-element">
                            <span class="placeholder">Sub-subcentro de Costo</span>
                            <select class="form-control" id="codigo_sscc" name="codigo_sscc" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($ssccs as $sscc): ?>
                                    <option value="<?php echo htmlspecialchars($sscc['codigo'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($sscc['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group1">
                        <label for="nombre_proyecto">Nombre del Proyecto</label>
                        <input type="text" class="form-control" id="nombre_proyecto" name="nombre_proyecto" required>
                    </div>
                    <div class="form-group1">
                        <label for="fecha_solicitud">Fecha de Solicitud</label>
                        <input type="date" class="form-control" id="fecha_solicitud" name="fecha_solicitud" required>
                    </div>
                    <div class="form-group1">
                        <label for="motivo_anticipo">Motivo del Anticipo</label>
                        <textarea class="form-control" id="motivo_anticipo" name="motivo_anticipo" required></textarea>
                    </div>
                    <div class="form-group1">
                        <label for="monto_total_solicitado">Monto Total Solicitado</label>
                        <input type="number" class="form-control" id="monto_total_solicitado" name="monto_total_solicitado" step="0.01" required>
                    </div>
                    <div class="form-group1">
                        <label for="jefe_aprobador">Jefe Aprobador</label>
                        <select class="form-control" id="jefe_aprobador" name="jefe_aprobador">
                            <option value="">Seleccione (opcional)</option>
                            <?php foreach ($jefes as $jefe): ?>
                                <option value="<?php echo htmlspecialchars($jefe['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($jefe['nombre_usuario'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</section>
<?php include "footer.php"; ?>