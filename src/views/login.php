<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="assets/styles/login.css">
    <link rel="stylesheet" href="assets/styles/modalAlert.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Oswald:wght@200;300;400&family=Quicksand:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <section class="general">
        <img src="assets/img/logo_color_teching.png" alt="Logo Teching" class="logo-teching">
        
        <form class="form-login" action="/proy_anticipos_rendiciones/login" method="POST">
        <h1 class="title">Sistema de Anticipos y Rendiciones</h1>    
        <p>Iniciar Sesión</p>
            <div class="form-container">
                <label for="form-usuario">Correo electrónico o nombre de usuario</label>
                <input type="text" name="form-usuario" id="form-usuario" class="form-container-input" placeholder="example@tech....">
            </div>
            <div class="form-container">
                <label for="form-contrasena">Contraseña</label>
                <input type="password" name="form-contrasena" id="form-contrasena" class="form-container-input" placeholder="********">
            </div>
            <!-- <a href="#">Olvidé mi contraseña</a> -->
            <input type="submit" value="Ingresar" id="btn-login" class="btn-login">
        </form>
    </section>

    <script src="assets/scripts/modalAlert.js"></script>
    <?php if ($error): ?>
        <script>
            // se debe enviar la configuración para la alerta
            showAlert({
                title: 'Mensaje de Error',
                message: '<?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>',
                type: 'error'
            });
        </script>
    <?php endif; ?>
</body>
</html>