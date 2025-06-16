/**/

document.addEventListener('DOMContentLoaded', () => {
    // Funciones para abrir y cerrar modales
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    // boton para agregar cc
    const btnAddCc = document.querySelector('.btn-add-cc');
    if (btnAddCc) {
        btnAddCc.addEventListener('click', () => openModal('addCcModal'));
    }

    // boton para agregar scc
    const btnAddScc = document.querySelector('.btn-add-scc');
    if (btnAddScc){
        btnAddScc.addEventListener('click', () => openModal('addSccModal'));
    }
    
    // boton para agregar sscc
    const btnAddSscc = document.querySelector('.btn-add-sscc');
    if(btnAddSscc){
        btnAddSscc.addEventListener('click', () => openModal('addSsccModal'));
    }
    

    // Botones "Cerrar" modal
    document.querySelectorAll('.btn-close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modal;
            closeModal(modalId);
        });
    });

    // Botones "Editar" para tb_cc
    const btnsEditCc = document.querySelectorAll('.btn-edit-cc');
    if(btnsEditCc){
        btnsEditCc.forEach(button => {
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
    }
    
    // Botones "Editar" para tb_scc
    const btnsEditScc = document.querySelectorAll('.btn-edit-scc');
    if(btnsEditScc){
        btnsEditScc.forEach(button => {
            button.addEventListener('click', () => {
                const row = button.closest('tr');
                const cells = row.querySelectorAll('td');
                const codigo = cells[0].textContent;
                const nombre = cells[1].textContent;
                const activo = cells[3].textContent === 'Sí' ? 1 : 0;

                // Hacer solicitud AJAX para obtener cc_codigo
                fetch('src/ajax/get_scc.php?codigo=' + encodeURIComponent(codigo))
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.log("Error al autocompletar los datos del elemento a editar")
                            return;
                        }

                        document.getElementById('edit_scc_codigo').value = codigo;
                        document.getElementById('edit_scc_nombre').value = nombre;
                        document.getElementById('edit_scc_activo').checked = activo === 1;
                        document.getElementById('edit_scc_cc_codigo').value = data.cc_codigo;
                        openModal('editSccModal');
                    })
                    .catch(error => {
                        alert(error, "No se pudo obtener el cc")
                    });
            });
        });
    }
    

    // botones "Editar" para tb_sscc
    const btnsEditSscc = document.querySelectorAll('.btn-edit-sscc');
    if(btnsEditSscc){
        btnsEditSscc.forEach(button => {
            button.addEventListener('click', () => {
                const row = button.closest('tr');
                const cells = row.querySelectorAll('td');
                const codigo = cells[0].textContent;
                const nombre = cells[1].textContent;
                const activo = cells[3].textContent === 'Sí' ? 1 : 0;

                // Hacer solicitud AJAX para obtener cc_codigo
                fetch('src/ajax/get_sscc.php?codigo=' + encodeURIComponent(codigo))
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.log("Error al autocompletar los datos del elemento a editar")
                            return;
                        }

                    document.getElementById('edit_sscc_codigo').value = codigo;
                    document.getElementById('edit_sscc_nombre').value = nombre;
                    document.getElementById('edit_sscc_activo').checked = activo === 1;
                    document.getElementById('edit_sscc_scc_codigo').value = data.scc_codigo;
                    openModal('editSsccModal');
                })
                .catch(error => {
                    alert(error, "No se pudo obtener el cc")
                });
            });
        });
    }

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
