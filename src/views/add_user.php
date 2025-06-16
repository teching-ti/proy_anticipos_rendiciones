<?php 
$hoja_de_estilos = "prueba.css?v=".time();
$titulo = "Agregar Usuario";
include "base.php";
?>
    <form class="form-login" action="/proy_anticipos_rendiciones/agregar_usuario" method="POST">
        <p>Agregar Usuario</p>
        <?php if ($error): ?>
            <script>
                Swal.fire({
                    title: 'Error',
                    text: '<?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>',
                    icon: 'error'
                });
            </script>
        <?php elseif ($success): ?>
            <script>
                Swal.fire({
                    title: 'Éxito',
                    text: '<?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>',
                    icon: 'success'
                }).then(() => {
                    window.location.href = '/proy_anticipos_rendiciones/dashboard';
                });
            </script>
        <?php endif; ?>
        <div class="form-container">
            <label for="nombre_usuario">Nombre de Usuario</label>
            <input type="text" name="nombre_usuario" id="nombre_usuario" class="form-container-input" value="<?php echo htmlspecialchars($_POST['nombre_usuario'] ?? '', ENT_QUOTES); ?>" required>
        </div>
        <div class="form-container">
            <label for="contrasena">Contraseña</label>
            <input type="password" name="contrasena" id="contrasena" class="form-container-input" required>
        </div>
        <div class="form-container">
            <label for="dni">DNI</label>
            <input type="text" name="dni" id="dni" class="form-container-input" value="<?php echo htmlspecialchars($_POST['dni'] ?? '', ENT_QUOTES); ?>" required>
        </div>
        <div class="form-container">
            <label for="rol">Rol</label>
            <select name="rol" id="rol" class="form-container-input" required>
                <option value="">Seleccione un rol</option>
                <?php foreach ($roles as $rol): ?>
                    <option value="<?php echo $rol['id']; ?>" <?php echo (isset($_POST['rol']) && $_POST['rol'] == $rol['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <input type="submit" value="Registrar" id="btn-login" class="btn-login">
    </form>
</div>
</main>
</html>