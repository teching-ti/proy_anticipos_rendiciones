/**
 * Muestra un modal de alerta con título, mensaje y tipo personalizados.
 * @param {Object} options - Opciones de la alerta.
 * @param {string} options.title - Título del modal (por defecto: 'Alerta').
 * @param {string} options.message - Mensaje a mostrar.
 * @param {string} options.type - Tipo de alerta: 'error', 'success', 'warning', 'info' (por defecto: 'info').
 */

function showAlert({ title = 'Alerta', message, type = 'info', event = ''} = {}) {
    // validar que se proporcione un mensaje
    if (!message) {
        console.error('showAlert: El mensaje es requerido');
        return;
    }

    const existingModal = document.getElementById('custom-alert-modal');
    if (existingModal) {
        existingModal.remove();
    }

    // Crear un nuevo contenedor del modal
    const modal = document.createElement('div');
    modal.id = 'custom-alert-modal';
    modal.className = 'custom-alert-modal';
    
    if (event === 'confirm') {
        modal.innerHTML = `
            <div class="custom-alert-content">
                <div class="modal-alert-header">
                    <p><i class="fa-solid fa-circle-exclamation fa-2xl" style="color: rgb(71, 113, 168)"></i></p>
                    <p id="custom-alert-title"></p>
                </div>
                <div class="modal-alert-body">
                    <p id="custom-alert-message"></p>
                    <div class="btn-confirm-container">
                        <button id="custom-alert-btn-aceptar" class="custom-alert-button">Aceptar</button>
                        <button id="custom-alert-btn-cancelar" class="custom-alert-button">Cancelar</button>
                    </div>
                </div>
            </div>
        `;
    } else if (event === 'envio') {
        modal.innerHTML = `
            <div class="custom-alert-content">
                <div class="modal-alert-header">
                    ${type=="success" ? "<p><i class='fa-solid fa-bell fa-2xl' style='color: #218838'></i></p><p><h3 id='custom-alert-title'></h3></p>": "<p><i class='fa-solid fa-triangle-exclamation fa-2xl' style='color: #e7c23a;'></i></p><p><h3 id='custom-alert-title'></h3></p>"}
                </div>
                <div class="modal-alert-body">
                    <p id="custom-alert-message"></p>
                    <button id="custom-alert-close" class="custom-alert-button" onclick="recargar()">Aceptar</button>
                </div>
            </div>
        `;
    } else if (event === 'error' || event === 'warning') {
        modal.innerHTML = `
            <div class="custom-alert-content">
                <div class="modal-alert-header">
                    <p><i class="fa-solid fa-triangle-exclamation fa-2xl" style="color: #e7c23a;"></i></p>
                    <p><h3 id="custom-alert-title"></h3></p>
                </div>
                <div class="modal-alert-body">
                    <p id="custom-alert-message"></p>
                    <button id="custom-alert-close" class="custom-alert-button"></button>
                </div>
            </div>
        `;
    } else {
        modal.innerHTML = `
            <div class="custom-alert-content">
                <div class="modal-alert-header">
                    <p><i class="fa-solid fa-bell fa-2xl" style="color: #218838"></i></p>
                    <p><h3 id="custom-alert-title"></h3></p>
                </div>
                <div class="modal-alert-body">
                    <p id="custom-alert-message"></p>
                    <button id="custom-alert-close" class="custom-alert-button"></button>
                </div>
            </div>
        `;
    }
        
    document.body.appendChild(modal);
    

    // Establecer el título, mensaje y tipo
    // const titleElement = document.getElementById('custom-alert-title');
    // const messageElement = document.getElementById('custom-alert-message');
    // const closeButton = document.getElementById('custom-alert-close');

    const titleElement = document.getElementById('custom-alert-title');
    const messageElement = document.getElementById('custom-alert-message');
    const closeButton = document.getElementById('custom-alert-close');
    const acceptButton = document.getElementById('custom-alert-btn-aceptar');
    const cancelButton = document.getElementById('custom-alert-btn-cancelar');

    titleElement.textContent = title;
    messageElement.textContent = message;

    if (acceptButton && cancelButton) {
        acceptButton.className = 'custom-alert-button';
        cancelButton.className = 'custom-alert-button';
        if (['error', 'success', 'warning', 'info'].includes(type)) {
            acceptButton.classList.add(`alert-${type}`);
            cancelButton.classList.add(`alert-${type}`);
        } else {
            acceptButton.classList.add('alert-info');
            cancelButton.classList.add('alert-cancel');
        }

        acceptButton.onclick = () => {
            modal.style.display = 'none';
        };
        cancelButton.onclick = () => {
            modal.style.display = 'none';
        };
    } else if (closeButton) {
        closeButton.textContent = 'Aceptar';
        closeButton.className = 'custom-alert-button';
        if (['error', 'success', 'warning', 'info'].includes(type)) {
            closeButton.classList.add(`alert-${type}`);
        } else {
            closeButton.classList.add('alert-info');
        }

        closeButton.onclick = () => {
            modal.style.display = 'none';
            if (event === 'envio') {
                location.reload();
            }
        };
    }
    
    // Mostrar el modal
    modal.style.display = 'flex';


    // Cerrar el modal al hacer clic fuera del contenido
    // modal.onclick = (event) => {
    //     if (event.target === modal) {
    //         modal.style.display = 'none';
    //     }
    // };

    //Cerrar con la tecla Escape
    document.addEventListener('keydown', function handler(event) {
        if (event.key === 'Escape') {
            modal.style.display = 'none';
            document.removeEventListener('keydown', handler);
        }
    });
}