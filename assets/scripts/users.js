document.addEventListener("DOMContentLoaded", function(){
    console.log("Vista de Usuarios")
    // Funciones para abrir y cerrar modales
    function openModal(modalId) {
        document.getElementById(modalId).style.display="block";
        //document.getElementById(modalId).classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display="none";
    }

     // Botón "Agregar Anticipo"
    document.querySelector('.btn-add-usuario').addEventListener('click', () => openModal('addUserModal'));

    // Botones "Cerrar" modal
    document.querySelectorAll('.btn-close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modal;
            closeModal(modalId);
        });
    });
});