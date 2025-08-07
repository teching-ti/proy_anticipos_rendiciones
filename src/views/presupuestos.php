<?php 
$hoja_de_estilos = "presupuestos.css?v=".time();
$titulo = "Presupuestos";
$fun = "presupuestos.js?v=".time();
include "base.php";
?>
<section class="presupuestos-content">
    <script src="assets/scripts/modalAlert.js"></script>
    
    <section class="section-table">

    <h2>Presupuestos</h2>
    <div id="btnCrearPresupuesto" class="btn btn-add-presupuesto"><i class="fa-solid fa-circle-plus fa-lg"></i> Crear presupuesto</div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-head">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Saldo Inicial</th>
                        <th>Saldo Final</th>
                        <th>Saldo Abonado</th>
                        <th>Saldo Disponible</th>
                        <th>Estado</th>
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                            <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($presupuestos)):?>
                        <tr><td>No hay presupuestos</td></tr>
                    <?php else: ?>
                        <?php foreach($presupuestos as $p):?>
                        <tr class="filas">
                            <td data-label="Codigo"><?= htmlspecialchars($p['codigo_sscc']); ?></td>
                            <td data-label="Nombre"><span><?= htmlspecialchars($p['nombre']); ?></span></td>
                            <td data-label="Saldo Incial"><?= htmlspecialchars($p['saldo_inicial']); ?></td>
                            <td data-label="Saldo Final"><?= htmlspecialchars($p['saldo_final']); ?></td>
                            <td data-label="Saldo Abonado"><?= htmlspecialchars($p['saldo_abonado']); ?></td>
                            <td data-label="Saldo Disponible"><?= htmlspecialchars($p['saldo_disponible']); ?></td>
                            <td data-label="Estado"><?= htmlspecialchars($p['activo'] == 1 ? 'Activo' : 'Inactivo'  ); ?></td>
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                                <td><div class="btn-add-fondos" data-id="<?= htmlspecialchars($p['id']); ?>"><i class="fa-solid fa-circle-plus"></i></div></td>
                            <?php endif; ?>
                        </tr> 
                        <?php endforeach; ?>
                    <?php endif;?>
                </tbody>
            </table>
        </div>
    </section>
    
    <!-- Modal para crear presupuesto -->
    <div id="createPresupuestoModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear nuevo presupuesto</h2>
                <div class="btn-close-modal" data-modal="createPresupuestoModal"><i class="fa-solid fa-lg fa-xmark"></i></div>
            </div>
            <div class="modal-body">
                <form id="createPresupuestoForm" action="/proy_anticipos_rendiciones/presupuesto_sscc/add" method="POST">
                    <div id="step-1" class="form-step active">
                        <div class="modal-element">
                            <label for="cc_codigo">Centro de Costo (CC):</label>
                            <select id="cc_codigo" name="cc_codigo" class="form-control" required>
                                <option value="">Seleccione un CC</option>
                            </select>
                        </div>
                        <div class="modal-element">
                            <label for="scc_codigo">Subcentro de Costo (SCC):</label>
                            <select id="scc_codigo" name="scc_codigo" class="form-control" required disabled>
                                <option value="">Seleccione un SCC</option>
                            </select>
                        </div>
                        <div class="modal-element">
                            <label for="sscc_codigo">Sub-Subcentro de Costo (SSCC):</label>
                            <select id="sscc_codigo" name="sscc_codigo" class="form-control" required disabled>
                                <option value="">Seleccione un SSCC</option>
                            </select>
                        </div>
                        <button type="button" id="btnNextStep" class="btn btn-default" disabled>Siguiente</button>
                    </div>
                    <div id="step-2" class="form-step" style="display: none;">
                        <div class="modal-element">
                            <label for="saldo_inicial">Saldo Inicial:</label>
                            <input type="number" id="saldo_inicial" name="saldo_inicial" class="form-control" step="0.01" required>
                        </div>
                        <div class="modal-element">
                            <label for="saldo_final">Saldo Final:</label>
                            <input type="number" id="saldo_final" name="saldo_final" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="modal-element">
                            <label for="saldo_disponible">Saldo Disponible:</label>
                            <input type="number" id="saldo_disponible" name="saldo_disponible" class="form-control" step="0.01" value="0">
                        </div>
                        <!-- No incluimos ultima_actualizacion ni activo, se manejan en el backend -->
                        <div class="modal-footer">
                            <button type="button" id="btnPrevStep" class="btn btn-default" style="display: none;">Atrás</button>
                            <button type="submit" id="btnSubmitPresupuesto" class="btn btn-default">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para añadir fondos -->
    <div id="addFundsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Añadir fondos</h2>
                <div class="btn-close-modal" data-modal="addFundsModal"><i class="fa-solid fa-lg fa-xmark"></i></div>
            </div>
            <div class="modal-body">
                <form id="addFundsForm">
                    <input type="hidden" id="presupuestoId" name="presupuestoId">
                    <div class="modal-element">
                        <label for="saldoDisponible">Saldo Disponible Actual:</label>
                        <input type="number" id="saldoDisponible" name="saldoDisponible" class="form-control" readonly>
                    </div>
                    <div class="modal-element">
                        <label for="montoAbono">Monto a Añadir:</label>
                        <input type="number" id="montoAbono" name="montoAbono" class="form-control" step="0.01" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="btnSaveFunds" class="btn btn-default">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</section>



<?php include "footer.php"; ?>