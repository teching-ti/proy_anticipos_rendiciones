/* Segunda prueba para validar que las alertas modales se ejecutan igual para todas las vistas */

document.addEventListener('DOMContentLoaded', () => {
    // Funciones para abrir y cerrar modales
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    // Botones "Agregar"
    document.querySelector('.btn-add-cc').addEventListener('click', () => openModal('addCcModal'));
    document.querySelector('.btn-add-scc').addEventListener('click', () => openModal('addSccModal'));
    document.querySelector('.btn-add-sscc').addEventListener('click', () => openModal('addSsccModal'));

    // Botones "Cerrar" modal
    document.querySelectorAll('.btn-close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modal;
            closeModal(modalId);
        });
    });

    // Botones "Editar" para tb_cc
    document.querySelectorAll('.btn-edit-cc').forEach(button => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            const cells = row.querySelectorAll('td');
            const codigo = cells[0].textContent;
            const nombre = cells[1].textContent;
            const activo = cells[3].textContent === 'Sí' ? 1 : 0;

            document.getElementById('edit_cc_codigo').value = codigo;
            document.getElementById('edit_cc_nombre').value = nombre;
            document.getElementById('edit_cc_activo').checked = activo === 1;
            openModal('editCcModal');
        });
    });

    // Botones "Editar" para tb_scc
    document.querySelectorAll('.btn-edit-scc').forEach(button => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            const cells = row.querySelectorAll('td');
            const codigo = cells[0].textContent;
            const nombre = cells[1].textContent;
            const activo = cells[4].textContent === 'Sí' ? 1 : 0;

            // Hacer solicitud AJAX para obtener cc_codigo
            fetch('centro_costos/get_scc?codigo=' + encodeURIComponent(codigo))
                .then(response => response.json())
                .then(data => {
                    if (!data || data.error) {
                        showAlert({
                            title: 'Error',
                            message: 'No se pudieron cargar los datos del subcentro.',
                            type: 'error'
                        });
                        return;
                    }

                    document.getElementById('edit_scc_codigo').value = data.codigo;
                    document.getElementById('edit_scc_nombre').value = data.nombre;
                    document.getElementById('edit_scc_activo').checked = data.activo == 1;
                    document.getElementById('edit_scc_cc_codigo').value = data.cc_codigo;
                    openModal('editSccModal');
                })
                .catch(error => {
                    showAlert({
                        title: 'Error',
                        message: 'Error al cargar los datos del subcentro: ' + error.message,
                        type: 'error'
                    });
                });
        });
    });

    // Botones "Editar" para tb_sscc
    document.querySelectorAll('.btn-edit-sscc').forEach(button => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            const cells = row.querySelectorAll('td');
            const codigo = cells[0].textContent;
            const nombre = cells[1].textContent;
            const activo = cells[4].textContent === 'Sí' ? 1 : 0;

            // Hacer solicitud AJAX para obtener scc_codigo
            fetch('centro_costos/get_sscc?codigo=' + encodeURIComponent(codigo))
                .then(response => response.json())
                .then(data => {
                    if (!data || data.error) {
                        showAlert({
                            title: 'Error',
                            message: 'No se pudieron cargar los datos del sub-subcentro.',
                            type: 'error'
                        });
                        return;
                    }

                    document.getElementById('edit_sscc_codigo').value = data.codigo;
                    document.getElementById('edit_sscc_nombre').value = data.nombre;
                    document.getElementById('edit_sscc_activo').checked = data.activo == 1;
                    document.getElementById('edit_sscc_scc_codigo').value = data.scc_codigo;
                    openModal('editSsccModal');
                })
                .catch(error => {
                    showAlert({
                        title: 'Error',
                        message: 'Error al cargar los datos del sub-subcentro: ' + error.message,
                        type: 'error'
                    });
                });
        });
    });

    // Cerrar modal al hacer clic fuera del contenido
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Cerrar modal con la tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.active').forEach(modal => {
                modal.classList.remove('active');
            });
        }
    });
});