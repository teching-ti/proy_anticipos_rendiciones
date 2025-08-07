document.addEventListener("DOMContentLoaded", function() {

    let categorias = [];
    fetch('tarifario/obtenerCategorias')
        .then(response => response.json())
        .then(data => {
            categorias = data;
            // Llenar formulario de agregar cargo (opcional, si lo necesitas)
            //const addMontosContainer = document.getElementById('add-montos-container');

            // categorias.forEach(categoria => {
            //     const div = document.createElement('div');
            //     div.className = 'modal-element';
            //     div.innerHTML = `
            //         <span class="placeholder">${(categoria.nombre)}</span>
            //         <input type="number" class="form-control" id="monto-${categoria.id}" name="montos[${categoria.id}]" value="0.00" step="0.01" required>
            //     `;
            //     addMontosContainer.appendChild(div);
            // });
        })
        .catch(error => {
            console.error('Error al cargar categorías:', error);
        });

    // Manejar clic en botones de editar
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', function() {
            const tarifarioId = this.getAttribute('data-id');
            console.log(tarifarioId );
            fetch(`tarifario/obtenerDetallesTarifario?tarifario_id=${tarifarioId}`)
                .then(response => response.json())
                .then(data => {

                    if (data.error) {
                        showAlert({ title: 'Error', message: data.error, type: 'error' });
                        return;
                    }
                    
                    document.getElementById("edit-id-tarifario").value = tarifarioId;
                    document.getElementById("edit-cargo-monto").value = data.cargo;
                    document.getElementById("edit-categoria-monto").value = data.categoria;
                    document.getElementById("edit-monto").value = data.monto;
                    
                    openModal("edit-monto-form");
                })
                .catch(error => {
                    showAlert({ title: 'Error', message: 'Error al cargar los montos', type: 'error' });
                });
        });
    });

    // Funciones para abrir y cerrar modales
    function openModal(modalId) {
        document.getElementById(modalId).style.display="block";
    }
    function closeModal(modalId) {
        document.getElementById(modalId).style.display="none";
    }
    
    // Cerrar formulario
    document.querySelectorAll('.btn-close-modal').forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-modal');
            closeModal(modalId);
        });
    });

    // Manejar envío del formulario de editar cargo
    document.getElementById('edit-monto-form-data').addEventListener('submit', function(e) {
        e.preventDefault();
        const idMontoTarifario = document.getElementById('edit-id-tarifario').value;
        const cargo = document.getElementById('edit-cargo-monto').value;
        const categoria = document.getElementById('edit-categoria-monto').value;
        const monto = document.getElementById('edit-monto').value;
        console.log('Monto a enviar:', monto);

        // Mostrar modal de confirmación
        showAlert({
            title: 'Confirmar Acción',
            message: `¿Estás seguro de actualizar a S/.${monto} el monto de ${categoria} para el cargo ${cargo}?`,
            type: 'confirm',
            event: 'confirm'
        });

        const acceptHandler = () => {
            fetch(`tarifario/actualizarMontos`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `tarifario_id=${encodeURIComponent(idMontoTarifario)}&monto=${encodeURIComponent(monto)}`
            })
            .then(response => {
                console.log('Respuesta del servidor:', response);
                return response.json();
            })
            .then(data => {
                console.log('Datos recibidos:', data);
                if (data.success) {
                    showAlert({
                        title: 'Éxito',
                        message: 'Monto actualizado exitosamente',
                        type: 'success',
                        event: 'envio'
                    });
                } else {
                    showAlert({
                        title: 'Error',
                        message: 'Error al actualizar los montos: ' + data.error,
                        type: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error en la solicitud:', error);
                showAlert({
                    title: 'Error',
                    message: `Error en la solicitud: ${error.message}`,
                    type: 'error'
                });
            });
        };
        
        // Agregar evento al botón Aceptar del modal
        const modal = document.getElementById('custom-alert-modal');
        modal.addEventListener('click', function handler(e) {
            if (e.target.id === 'custom-alert-btn-aceptar') {
                acceptHandler();
            } else if (e.target.id === 'custom-alert-btn-cancelar') {
                modal.style.display = 'none';
                document.getElementById('edit-cargo-form').style.display = 'none';
                modal.removeEventListener('click', handler);
            }
        });
    });

});