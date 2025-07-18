<?php 
$hoja_de_estilos = "cost_center.css?v=".time();
$titulo = "Centros de Costo";
$fun = "cost_center.js?v=".time();
include "base.php";
?>
<script src="assets/scripts/modalAlert.js"></script>
<section class="cost-center-content">
    <!-- Habilitación de alerta modal -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            showAlert({
                title: 'Éxito',
                message: '<?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); ?>',
                type: 'success'
            });
        </script>
        <!-- Limpieza de la variable para success -->
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <script>
            showAlert({
                title: 'Error',
                message: '<?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); ?>',
                type: 'error'
            });
        </script>
        <!-- Limpieza de la variable para error -->
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- tabla para tb_cc -->
    <section class="section-table">
        <h2>Centros de Costo</h2>
        <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
            <button type='button' class='btn btn-add-cc'><i class='fa-solid fa-circle-plus fa-lg'></i></button>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-head">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Nombre Corto</th>
                        <th>Activo</th>
                        <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                            <th>Editar</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="table-body">
                    <?php if (empty($cc_data)): ?>
                        <tr><td colspan="4">No hay datos disponibles</td></tr>
                    <?php else: ?>
                        <?php foreach ($cc_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_corto'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $row['activo'] ? 'Sí' : 'No'; ?></td>
                                <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                                    <td class="td-action">
                                        <button type="button" class="btn btn-edit-cc"><i class="fa-solid fa-pen-to-square"></i></button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Tabla para tb_scc -->
    <section class="section-table">
        <h2>Subcentros de Costo</h2>
        <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
            <button type='button' class='btn btn-add-scc'><i class='fa-solid fa-circle-plus fa-lg'></i></button>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-head">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Nombre Corto</th>
                        <th>Activo</th>
                        <th>Centro de Costo</th>
                        <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                            <th>Editar</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="table-body">
                    <?php if (empty($scc_data)): ?>
                        <tr><td colspan="5">No hay datos disponibles</td></tr>
                    <?php else: ?>
                        <?php foreach ($scc_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_corto'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $row['activo'] ? 'Sí' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars($row['cc_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                                    <td class="td-action">
                                        <button type="button" class="btn btn-edit-scc"><i class="fa-solid fa-pen-to-square"></i></button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Tabla para tb_sscc -->
    <section class="section-table">
        <h2>Sub-subcentros de Costo</h2>
        <?php if ($_SESSION['rol']==1 || $_SESSION['rol']==4): ?>
            <button type='button' class='btn btn-add-sscc'><i class='fa-solid fa-circle-plus'></i></button>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-head">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Nombre Corto</th>
                        <th>Activo</th>
                        <th>Subcentro de Costo</th>
                        <?php if ($_SESSION['rol']==1 || $_SESSION['rol']==4): ?>
                            <th>Editar</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="table-body">
                    <?php if (empty($sscc_data)): ?>
                        <tr><td colspan="5">No hay datos disponibles</td></tr>
                    <?php else: ?>
                        <?php foreach ($sscc_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_corto'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo $row['activo'] ? 'Sí' : 'No'; ?></td>
                                <td><?php echo htmlspecialchars($row['scc_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <?php if ($_SESSION['rol']==1 || $_SESSION['rol']==4): ?>
                                    <td class="td-action">
                                        <button type="button" class="btn btn-edit-sscc"><i class="fa-solid fa-pen-to-square"></i></button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    
    <?php if ($_SESSION['rol']==1 || $_SESSION['rol']==4): ?>
    <!-- Modal para agregar cc -->
    <div class='modal' id='addCcModal'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5>Agregar Centro de Costo</h5>
                <button type='button' class='modal-close btn-close-modal' data-modal='addCcModal'><i class='fa-solid fa-xmark fa-lg'></i></button>
            </div>
            <form action='cost_center/add_cc' method='POST'>
                <div class='modal-body'>
                    <div class='container-input'>
                        <label for='cc_codigo'>Código</label>
                        <input type='text' id='cc_codigo' name='codigo' required>
                    </div>
                    <div class='container-input'>
                        <label for='cc_nombre'>Nombre</label>
                        <input type='text' id='cc_nombre' name='nombre' required>
                    </div>
                    <div class='container-input'>
                        <label for='cc_nombre_corto'>Nombre Corto</label>
                        <input type='text' id='cc_nombre_corto' name='nombre_corto' required>
                    </div>
                    <div class='container-input form-check'>
                        <input type='checkbox' id='cc_activo' name='activo' value='1' checked>
                        <label for='cc_activo'>Activo</label>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='submit' class='btn'>Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    endif;
    ?>
    


    <?php 
    if ($_SESSION['rol']==1 || $_SESSION['rol']==4):
    ?>
    <!-- Modal para editar cc -->
    <div class='modal' id='editCcModal'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5>Editar Centro de Costo</h5>
                <button type='button' class='modal-close btn-close-modal' data-modal='editCcModal'><i class='fa-solid fa-xmark fa-lg'></i></button>
            </div>
            <form action='cost_center/edit_cc' method='POST'>
                <div class='modal-body'>
                    <div class='container-input'>
                        <label for='edit_cc_codigo'>Código</label>
                        <input type='text' id='edit_cc_codigo' name='codigo' readonly>
                    </div>
                    <div class='container-input'>
                        <label for='edit_cc_nombre'>Nombre</label>
                        <input type='text' id='edit_cc_nombre' name='nombre' required>
                    </div>
                    <div class='container-input form-check'>
                        <input type='checkbox' id='edit_cc_activo' name='activo' value='1'>
                        <label for='edit_cc_activo'>Activo</label>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='submit' class='btn'>Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <?php 
    endif;
    ?>

    <?php 
    if ($_SESSION['rol']==1 || $_SESSION['rol']==4):
    ?>
    <!-- Modal para editar tb_scc -->
    <div class='modal' id='editSccModal'>
        <div class='modal-content'>
            <div class='modal-header'>
                <h5>Editar Subcentro de Costo</h5>
                <button type='button' class='modal-close btn-close-modal' data-modal='editSccModal'>
                    <i class='fa-solid fa-xmark fa-lg'></i>
                </button>
            </div>
            <form action='cost_center/edit_scc' method='POST'>
                <div class='modal-body'>
                    <div class='container-input'>
                        <label for='edit_scc_cc_codigo'>Centro de Costo</label>
                        <select id='edit_scc_cc_codigo' name='cc_codigo' required>
                            <option value=''>Seleccione un centro de costo</option>
                            <?php foreach ($cc_list as $cc): ?>
                                <option value='<?php echo htmlspecialchars($cc['codigo'], ENT_QUOTES, 'UTF-8'); ?>'>
                                    <?php echo htmlspecialchars($cc['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class='container-input'>
                        <label for='edit_scc_codigo'>Código SCC</label>
                        <input type='text' id='edit_scc_codigo' name='codigo' readonly>
                    </div>
                    <div class='container-input'>
                        <label for='edit_scc_nombre'>Nombre</label>
                        <input type='text' id='edit_scc_nombre' name='nombre' required>
                    </div>
                    <div class='container-input form-check'>
                        <input type='checkbox' id='edit_scc_activo' name='activo' value='1'>
                        <label for='edit_scc_activo'>Activo</label>
                    </div>
                </div>
                <div class='modal-footer'>
                    <button type='submit' class='btn'>Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <?php 
    endif;
    ?>

    <?php if ($_SESSION['rol']==1 || $_SESSION['rol']==4): ?>
    <!-- Modal para agregar tb_scc -->
    <div class="modal" id="addSccModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Agregar Subcentro de Costo</h5>
                <button type="button" class="modal-close btn-close-modal" data-modal=addSccModal><i class="fa-solid fa-xmark fa-lg"></i></button>
            </div>
            <form action="cost_center/add_scc" method="POST">
                <div class="modal-body">
                    <div class="container-input">
                        <label for="scc_cc_codigo">Centro de Costo</label>
                        <select id="scc_cc_codigo" name="cc_codigo" required>
                            <option value="">Seleccione un centro de costo</option>
                            <?php foreach ($cc_list as $cc): ?>
                                <option value="<?php echo htmlspecialchars($cc['codigo'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($cc['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="container-input">
                        <label for="scc_codigo">Código SCC</label>
                        <input type="text" id="scc_codigo" name="codigo" required>
                    </div>
                    <div class="container-input">
                        <label for="scc_nombre">Nombre</label>
                        <input type="text" id="scc_nombre" name="nombre" required>
                    </div>
                    <div class="container-input">
                        <label for="scc_nombre_corto">Nombre Corto</label>
                        <input type="text" id="scc_nombre_corto" name="nombre_corto" required>
                    </div>
                    <div class="container-input form-check">
                        <input type="checkbox" id="scc_activo" name="activo" value="1" checked>
                        <label for="scc_activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($_SESSION['rol']==1 || $_SESSION['rol']==4): ?>
    <!-- Modal para editar tb_sscc -->
    <div class="modal" id="editSsccModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Editar Sub-subcentro de Costo</h5>
                <button type="button" class="modal-close btn-close-modal" data-modal="editSsccModal"><i class="fa-solid fa-xmark fa-lg"></i></button>
            </div>
            <form action="cost_center/edit_sscc" method="POST">
                <div class="modal-body">
                    <div class="container-input">
                        <label for="edit_sscc_scc_codigo">Subcentro de Costo</label>
                        <select id="edit_sscc_scc_codigo" name="scc_codigo" required>
                            <option value="">Seleccione un subcentro de costo</option>
                            <?php foreach ($scc_list as $scc): ?>
                                <option value="<?php echo htmlspecialchars($scc['codigo'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($scc['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="container-input">
                        <label for="edit_sscc_codigo">Código SSCC</label>
                        <input type="text" id="edit_sscc_codigo" name="codigo" readonly>
                    </div>
                    <div class="container-input">
                        <label for="edit_sscc_nombre">Nombre</label>
                        <input type="text" id="edit_sscc_nombre" name="nombre" required>
                    </div>
                    <div class="container-input form-check">
                        <input type="checkbox" id="edit_sscc_activo" name="activo" value="1">
                        <label for="edit_sscc_activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($_SESSION['rol']==1 || $_SESSION['rol']==4): ?>
    <!-- Modal para agregar tb_sscc -->
    <div class="modal" id="addSsccModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Agregar Sub-subcentro de Costo</h5>
                <button type="button" class="modal-close btn-close-modal" data-modal="addSsccModal"><i class="fa-solid fa-xmark fa-lg"></i></button>
            </div>
            <form action="cost_center/add_sscc" method="POST">
                <div class="modal-body">
                    <div class="container-input">
                        <label for="sscc_scc_codigo">Subcentro de Costo</label>
                        <select id="sscc_scc_codigo" name="scc_codigo" required>
                            <option value="">Seleccione un subcentro de costo</option>
                            <?php foreach ($scc_list as $scc): ?>
                                <option value="<?php echo htmlspecialchars($scc['codigo'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($scc['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="container-input">
                        <label for="sscc_codigo">Código SSCC</label>
                        <input type="text" id="sscc_codigo" name="codigo" required>
                    </div>
                    <div class="container-input">
                        <label for="sscc_nombre">Nombre</label>
                        <input type="text" id="sscc_nombre" name="nombre" required>
                    </div>
                    <div class="container-input">
                        <label for="sscc_nombre_corto">Nombre Corto</label>
                        <input type="text" id="sscc_nombre_corto" name="nombre_corto" required>
                    </div>
                    
                    <div class="container-input form-check">
                        <input type="checkbox" id="sscc_activo" name="activo" value="1" checked>
                        <label for="sscc_activo">Activo</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</section>
<?php include "footer.php"; ?>