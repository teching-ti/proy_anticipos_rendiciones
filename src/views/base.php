<?php
// Evitar caché del navegador
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Desactivar indexado -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Desactivar Cache de los Navegadores -->
    <!-- <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0"> -->

    
    <!-- FavIcon -->
    <link rel="shortcut icon" href="assets/img/favicon.ico" type="image/x-icon">
    <!-- Page tiitle -->
    <title><?= htmlspecialchars($titulo)?></title>
    <!-- Estilos -->
    <link rel="stylesheet" href="assets/styles/base.css?v=<?=time();?>">
    <link rel="stylesheet" href="assets/styles/modalAlert.css?v=<?=time();?>">
    <link rel="stylesheet" href="assets/styles/<?= htmlspecialchars($hoja_de_estilos) ?>">
    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&family=Oswald:wght@200;300;400&family=Quicksand:wght@700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
</head>
<body>
<main>

<div class="container">
    <aside class="sidebar" id="sidebar">
        <nav class="sidebar-nav">
            <ul class='sidebar-menu'>
                <li class="logo-teching">
                    <img src="assets/img/logo_color_teching.png" alt="Logo Teching" id="img-logo">
                </li>
                <li>
                    <a href="dashboard" class="link-text">
                        <i class="fas fa-home"></i>
                        <span>Inicio</span>
                    </a>
                </li>
                <li>
                    <a href="anticipos" class="link-text">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        <span>Anticipos</span>
                    </a>
                </li>

                <li>
                    <a href='rendiciones' class='link-text'>
                        <i class='fa-solid fa-circle-dollar-to-slot'></i>
                        <span>Rendiciones</span>
                    </a>
                </li>
                <?php
                    if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4){
                        echo "
                        <li>
                            <a href='usuarios' class='link-text'>
                                <i class='fa-solid fa-users-line'></i>
                                <span>Usuarios</span>
                            </a>
                        </li>";
                    }
                ?>
                
                <li>
                    <a href='centro_costos' class='link-text'>
                        <i class='fa-solid fa-coins'></i>
                        <span>Centro de Costos</span>
                    </a>
                </li>
                <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                    <li>
                        <a href='presupuestos' class='link-text'>
                            <i class='fa-solid fa-money-bill-wave'></i>
                            <span>Presupuestos</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                    <li>
                        <a href='tarifario' class='link-text'>
                            <i class="fa-solid fa-clipboard-list"></i>
                            <span>Tarifario</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </aside>

    <!-- Menú responsive oculto -->
    <div class="responsive-menu" id="responsive-menu">
        <nav class="responsive-nav">
            <ul class="responsive-menu-list">
                <li class="logo-teching">
                    <img src="assets/img/logo_color_teching.png" alt="Logo Teching" id="img-logo">
                </li>
                <li><a href="dashboard">Inicio</a></li>
                <li><a href="anticipos">Anticipos</a></li>
                <li><a href="rendiciones">Rendiciones</a></li>
                <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                    <li><a href="usuarios">Usuarios</a></li>
                <?php endif; ?>
                <li><a href="centro_costos">Centro de Costos</a></li>
                <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                    <li><a href="presupuestos">Presupuestos</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                    <li><a href="tarifario">Tarifario</a></li>
                <?php endif; ?>
                
            </ul>
            <div class="close-responsive-menu" id="close-responsive-menu">
                <span><i class="fa-regular fa-circle-xmark fa-xl"></i></span>
            </div>
        </nav>
    </div>
    
    <article class="principal-content">
        <header class="header">
            <button class="btn-menu-responsive" id="open-responsive-menu">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div id="user-first-info" class="user-first-info" data-info = "<?php echo htmlspecialchars($_SESSION['rol'], ENT_QUOTES, 'UTF-8');?>" data-user = <?php echo htmlspecialchars($_SESSION['id'], ENT_QUOTES, 'UTF-8');?> >
                <?php echo htmlspecialchars($_SESSION['nombre_usuario'], ENT_QUOTES, 'UTF-8'); ?>
                <i class="fa-solid fa-caret-down"></i>
            </div>
            <div class="user-dropdown" id="user-dropdown">
                <p><strong>Departamento:</strong> <?php echo htmlspecialchars($_SESSION['trabajador']['departamento_nombre'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></p>    
                <p><strong>Cargo:</strong> <?php echo htmlspecialchars($_SESSION['trabajador']['cargo'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Correo:</strong> <?php echo htmlspecialchars($_SESSION['trabajador']['correo'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></p>
                <p><strong>Rol:</strong> <?php echo htmlspecialchars($_SESSION['rol_nombre'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p id="base-dni-user" style="visibility: hidden; display: none;"><?php echo htmlspecialchars($_SESSION['dni'], ENT_QUOTES, 'UTF-8');?></p>
                <p class="line-exit-session">
                    <a href="/proy_anticipos_rendiciones/logout" class="exit-session">
                        <i class="fas fa-sign-out-alt"></i><span>Salir</span>
                    </a>
                </p>
            </div>
        </header>
        