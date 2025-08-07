<?php 
$hoja_de_estilos = "tarifario.css?v=".time();
$titulo = "Tarifario";
$fun = "tarifario.js?v=".time();
include "base.php";

// Limpiar mensajes de sesión
unset($_SESSION['success'], $_SESSION['error']);
?>
<section>
    <!-- Incluir alert.js -->
    <script src="assets/scripts/modalAlert.js"></script>

    <!-- Tabla de Tarifario -->
        <div class="container-table">
            <h2>Tarifario de Viáticos</h2>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-head">
                        <tr>
                            <th>Id.</th>
                            <th>Cargo</th>
                            <th>Categoría</th>
                            <th>Monto</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php foreach ($tarifario as $tarifa): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tarifa['id']); ?></td>
                                <td><?php echo htmlspecialchars($tarifa['cargo']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($tarifa['categoria'])); ?></td>
                                <td><?php echo number_format($tarifa['monto'], 2); ?></td>
                                <td><div class="btn-edit" data-id="<?php echo $tarifa['id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Editar</div></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <div class="content-tables">

        
        <!-- Tabla de Cargos Tarifario -->
        <div class="container-table" id="cargos-tarifario">
            <div class="container-table-header">
                <h2>Cargos Tarifario</h2>
                <div class="btn-add-cargos"><i class="fa-solid fa-circle-plus"></i></div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-head">
                        <tr>
                            <th>Id.</th>
                            <th>Nombre del cargo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <?php foreach ($allCargos as $cargo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cargo['id']); ?></td>
                                <td><?php echo htmlspecialchars($cargo['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($cargo['activo']==1 ? 'Habilitado' : 'Inhabilitado');?></td>
                                <td><div class="btn-edit" data-id="<?php echo $cargo['id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Editar</div></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Formulario para agregar cargo -->
    <div id="add-cargo-form" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Agregar Nuevo Cargo</h3>
                <div class="btn-close-modal" data-modal="add-cargo-form"><i class="fa-solid fa-lg fa-xmark"></i></div>
            </div>
            <form id="cargo-form">
                <div class="modal-body">
                    <div class="modal-element">
                        <span class="placeholder">Nombre del Cargo</span>
                        <input type="text" class="form-control" id="cargo-nombre" name="cargo-nombre">
                    </div>
                    <?php foreach ($categorias as $categoria): ?>
                        <div class="modal-element">
                            <span class="placeholder"><?php echo ucfirst(htmlspecialchars($categoria['nombre'])); ?></span>
                            <input type="number" class="form-control" id="monto-<?php echo $categoria['id']; ?>" name="montos[<?php echo $categoria['id']; ?>]" value="0.00" step="0.01" required>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-guardar">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Formulario para editar montos -->
    <div id="edit-monto-form" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar monto</h3>
                <div class="btn-close-modal" data-modal="edit-monto-form"><i class="fa-solid fa-lg fa-xmark"></i></div>
            </div>
            <form id="edit-monto-form-data">
                <div class="modal-body" id="edit-monto-container">
                    <input type="text" id="edit-id-tarifario" readonly>
                    <div class="modal-element">
                        <span class="placeholder">Cargo</span>
                        <input type="text" name="edit-cargo-monto" id="edit-cargo-monto" readonly>
                    </div>
                    
                    <div class="modal-element">
                        <span class="placeholder">Categoría</span>
                        <input type="text" name="edit-categoria-monto" id="edit-categoria-monto" readonly>
                    </div>

                    <div class="modal-element">
                        <span class="placeholder">Monto</span>
                        <input type="number" name="edit-monto" id="edit-monto" placeholder="0.00" min="10" max="99" step="0.1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-guardar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

</section>
<?php include "footer.php"; ?>