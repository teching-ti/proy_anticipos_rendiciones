document.addEventListener('DOMContentLoaded', () => {
    const userInfo = document.getElementById('user-first-info');
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
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            dropdown.classList.remove('active');
            userInfo.classList.remove('active');
        }
    });

     document.getElementById('open-responsive-menu').addEventListener('click', function () {
        document.getElementById('responsive-menu').classList.add('show');
    });

    document.getElementById('close-responsive-menu').addEventListener('click', function () {
        document.getElementById('responsive-menu').classList.remove('show');
    });
});