<?php 
$hoja_de_estilos = "users.css?v=".time();
$titulo = "Usuarios";
$fun = "users.js?v=".time();
include "base.php";
?>

<section class="users-content">
    <!-- Incluir alert.js -->
    <script src="assets/scripts/modalAlert.js"></script>

    <!-- Tabla de usuarios -->
    <section class="section-table">
        <h2>Listado de Usuarios</h2>
        <div class="elemento-agregar-usuario">
            <div class="btn btn-add-usuario"><i class='fa-solid fa-circle-plus'></i> Nuevo usuario</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-head">
                    <tr>
                        <th>Nombre de Usuario</th>
                        <th>DNI</th>
                        <th>Rol</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users_data)): ?>
                        <tr><td colspan="4">No hay usuarios registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($users_data as $user): ?>
                            <tr>
                                <td data-label="Nombre de Usuario"><?php echo htmlspecialchars($user['nombre_usuario'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="DNI"><?php echo htmlspecialchars($user['dni'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Rol"><?php echo htmlspecialchars($user['rol_nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td data-label="Estado"><?php echo htmlspecialchars($user['estado'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Modal para Agregar Anticipo -->
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Agregar usuario</h2>
                <button type="button" class="btn-close-modal" data-modal="addUserModal"><i class="fa-solid fa-lg fa-xmark"></i></button>
            </div>
            <form action="agregar_usuario" method="POST">
                <div class="modal-body">
                    <div class="modal-elements-container">
                        <div class="modal-element doc-id">
                            <span class="placeholder">Documento de Identidad</span>
                            <span class="input-icon-left">
                                <i class="fa-solid fa-id-card"></i>
                            </span>
                            <input type="text" class="form-control" id="doc-identidad" name="doc-identidad">
                            <span class="lupa">
                                <i class="fa-solid fa-lg fa-magnifying-glass"></i>
                            </span>
                        </div>
                        <div class="modal-element">
                            <span class="input-icon-left">
                                <i class="fa-solid fa-user-tie"></i>
                            </span>
                            <span class="placeholder">Usuario</span>
                            <input type="text" class="form-control" id="user-nombre" name="user-nombre" readonly>
                        </div>
                        <div class="modal-element">
                            <span class="input-icon-left">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <span class="placeholder">Contraseña</span>
                            <input type="text" class="form-control" id="user-contra" name="user-contra" readonly>
                        </div>
                        <div class="modal-element">
                            <span class="input-icon-left">
                                <i class="fa-solid fa-pen-nib"></i>
                            </span>
                            <span class="placeholder">Rol</span>
                            <select class="form-control" id="user-rol" name="user-rol">
                                <?php foreach ($roles as $rol):?>
                                        <option value="<?php echo htmlspecialchars($rol['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                <?php endforeach ;?>
                            </select>
                        </div>
                        <div class="modal-element">
                            <span class="input-icon-left">
                                <i class="fa-solid fa-circle-user"></i>
                            </span>
                            <span class="placeholder">Nombres</span>
                            <input type="text" class="form-control" id="user-nombre-completo" name="user-nombre-completo" disabled>
                        </div>
                        <div class="modal-element">
                            <span class="input-icon-left">
                                <i class="fa-solid fa-briefcase"></i>
                            </span>
                            <span class="placeholder">Cargo</span>
                            <input type="text" class="form-control" id="user-cargo" name="user-cargo" disabled>
                        </div>
                        <div class="modal-element">
                            <span class="input-icon-left">
                                <i class="fa-solid fa-tag"></i>
                            </span>
                            <span class="placeholder">Departameto</span>
                            <input type="text" class="form-control" id="user-departamento" name="user-departamento" disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-guardar-usuario">Guardar <i class="fa-solid fa-floppy-disk"></i></button>
                    <button type="submit" class="btn btn-limpiar">Limpiar<i class="fa-solid fa-eraser"></i></button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>