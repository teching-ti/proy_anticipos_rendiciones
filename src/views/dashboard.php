<?php 
$hoja_de_estilos = "dashboard.css?v=".time();
$titulo = "Dashboard";
$fun = "dashboard.js?v=".time();
include "base.php";
?>
    <!-- <button class="menu-toggle" id="menu-toggle"><i class="fas fa-bars"></i></button> -->
        <!-- sección para mostrar datos del trabajador -->
        <section class="principal-info">
            <h1 class="main-title">Panel Principal</h1>
            <!-- tarjetas para accesos rápidos-->
            <div class="dashboard-cards">
                <div class="card">
                    <h2>Anticipos Solicitados</h2>
                    <p class="card-number"><?=$cantidad_anticipos['cantidad_solicitudes'];?></p>
                </div>
                <div class="card">
                    <h2>Anticipos Completados</h2>
                    <p class="card-number"><?=$cantidad_rendido['cantidad'];?></p>
                </div>
                <div class="card">
                    <h2>Anticipos Observados</h2>
                    <p class="card-number"><?=$cantidad_observado['cantidad'];?></p>
                </div>
                <div class="card">
                    <h2>Anticipos Autorizados</h2>
                    <p class="card-number"><?=$cantidad_autorizado['cantidad'];?></p>
                </div>
            </div>
        </section>
        <section class="principal-info">
            <h1 class="main-title">Accesos Rápidos</h1>
            <div class="dashboard-cards">
                <a class="fast-access-card" id="nuevo-anticipo">
                    <span class="card-icon">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </span>
                    <p class="fast-access-text">Nuevo Anticipo</p>
                </a>
                <a class="fast-access-card" id="nueva-rendicion">
                    <span class="card-icon">
                    <i class="fa-solid fa-circle-dollar-to-slot"></i>
                    </span>
                    <p class="fast-access-text">Nueva Rendición</p>
                </a>
                <?php
                    if($_SESSION['rol']==1){
                        echo "<a class='fast-access-card' id='nuevo-usuario' href='/proy_anticipos_rendiciones/agregar_usuario'>
                            <span class='card-icon'>
                            <i class='fa-solid fa-user-plus'></i>
                            </span>
                            <p class='fast-access-text'>Nuevo Usuario</p>
                        </a>";
                    }
                ?>
                <?php
                if($_SESSION['rol']==1){
                    echo "<a class='fast-access-card' id='nuevo-sscc'>
                        <span class='card-icon'>
                        <i class='fa-solid fa-pen-ruler'></i>
                        </span>
                        <p class='fast-access-text'>Nuevo SSCC</p>
                    </a";
                }
                ?>
            </div>
        </section>
    </article>

<?php include "footer.php"; ?>