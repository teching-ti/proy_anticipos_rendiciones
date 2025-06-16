document.addEventListener('DOMContentLoaded', () => {
    // Funciones para abrir y cerrar modales
    function openModal(modalId) {
        document.getElementById(modalId).style.display="block";
        //document.getElementById(modalId).classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display="none";
    }

    // Validación de campos (solo letras, números y espacios)
    function validateInput(input) {
    const regex = /^[a-zA-Z0-9\sáéíóúÁÉÍÓÚñÑ&]+$/;
    return regex.test(input);
}

    // Validar formulario de agregar anticipo
    function validateAddForm(form) {
        const fields = ['solicitante', 'area', 'cargo', 'nombre_proyecto', 'motivo_anticipo'];
        for (const field of fields) {
            const value = form.querySelector(`[name="${field}"]`).value.trim();
            if (!validateInput(value)) {
                showAlert({
                    title: 'Error',
                    message: `El campo ${field.replace('_', ' ')} solo puede contener letras, números y espacios.`,
                    type: 'error'
                });
                return false;
            }
        }
        const dni = form.querySelector('[name="dni_solicitante"]').value.trim();
        if (!/^[0-9]{8}$/.test(dni)) {
            showAlert({
                title: 'Error',
                message: 'El DNI del solicitante debe tener 8 dígitos.',
                type: 'error'
            });
            return false;
        }
        const monto = form.querySelector('[name="monto_total_solicitado"]').value;
        if (monto <= 0) {
            showAlert({
                title: 'Error',
                message: 'El monto debe ser mayor a 0.',
                type: 'error'
            });
            return false;
        }
        const fecha = form.querySelector('[name="fecha_solicitud"]').value;
        if (!/^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
            showAlert({
                title: 'Error',
                message: 'La fecha de solicitud debe tener el formato YYYY-MM-DD.',
                type: 'error'
            });
            return false;
        }
        return true;
    }

    // Validar formularios de aprobar/rechazar
    function validateActionForm(form) {
        const comentario = form.querySelector('[name="comentario"]').value.trim();
        if (comentario && !validateInput(comentario)) {
            showAlert({
                title: 'Error',
                message: 'El comentario solo puede contener letras, números y espacios.',
                type: 'error'
            });
            return false;
        }
        return true;
    }

    // Validar formulario de agregar anticipo
    const addForm = document.querySelector('#addAnticipoModal form');
    if (addForm) {
        addForm.addEventListener('submit', (e) => {
            if (!validateAddForm(addForm)) {
                e.preventDefault();
            }
        });
    }

    // Validar formularios de aprobar/rechazar
    const actionForms = document.querySelectorAll('form[action*="/anticipos/approve"], form[action*="/anticipos/reject"]');
    actionForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!validateActionForm(form)) {
                e.preventDefault();
            }
        });
    });

    // Botón "Agregar Anticipo"
    document.querySelector('.btn-add-anticipo').addEventListener('click', () => openModal('addAnticipoModal'));

    // Botones "Cerrar" modal
    document.querySelectorAll('.btn-close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modal;
            closeModal(modalId);
        });
    });
});