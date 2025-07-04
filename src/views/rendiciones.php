<?php 
$hoja_de_estilos = "rendiciones.css?v=".time();
$titulo = "Rendiciones";
$fun = "rendiciones.js?v=".time();
include "base.php";

// Limpiar mensajes de sesión
unset($_SESSION['success'], $_SESSION['error']);
?>
    <h2>Vista de rendiciones</h2>
<?php include "footer.php"; ?>