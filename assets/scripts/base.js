document.addEventListener('DOMContentLoaded', () => {
    // Elemento donde se coloca el nombre del usuario
    const userInfo = document.getElementById('user-first-info');
    // Elemento que mostrará la información completa del usuario logueado
    const dropdown = document.getElementById('user-dropdown');

    userInfo.addEventListener('click', (e) => {
        e.stopPropagation(); // Evita que el clic cierre el desplegable inmediatamente
        dropdown.classList.toggle('active');
        userInfo.classList.toggle('active');
    });

    // Cierra el desplegable si se hace clic fuera
    document.addEventListener('click', (e) => {
        if (!userInfo.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
            userInfo.classList.remove('active');
        }
    });
    
    // Cierra el desplegable si se presiona la tecla escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            dropdown.classList.remove('active');
            userInfo.classList.remove('active');
        }
    });

    // funcionalidad que permitirá abrir el menu responsive
    document.getElementById('open-responsive-menu').addEventListener('click', function () {
        document.getElementById('responsive-menu').classList.add('show');
    });

    // funcionalidad que permitirá cerrar el menu responsive
    document.getElementById('close-responsive-menu').addEventListener('click', function () {
        document.getElementById('responsive-menu').classList.remove('show');
    });
});