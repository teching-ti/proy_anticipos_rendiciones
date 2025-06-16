/**
 * Muestra un modal de alerta con título, mensaje y tipo personalizados.
 * @param {Object} options - Opciones de la alerta.
 * @param {string} options.title - Título del modal (por defecto: 'Alerta').
 * @param {string} options.message - Mensaje a mostrar.
 * @param {string} options.type - Tipo de alerta: 'error', 'success', 'warning', 'info' (por defecto: 'info').
 */

function showAlert({ title = 'Alerta', message, type = 'info' } = {}) {
    // validar que se proporcione un mensaje
    if (!message) {
        console.error('showAlert: El mensaje es requerido');
        return;
    }

    // Crear el contenedor del modal si no existe
    let modal = document.getElementById('custom-alert-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'custom-alert-modal';
        modal.className = 'custom-alert-modal';
        modal.innerHTML = `
            <div class="custom-alert-content">
                <h2 id="custom-alert-title"></h2>
                <p id="custom-alert-message"></p>
                <button id="custom-alert-close" class="custom-alert-button"></button>
            </div>
        `;
        document.body.appendChild(modal);
    }

    // Establecer el título, mensaje y tipo
    const titleElement = document.getElementById('custom-alert-title');
    const messageElement = document.getElementById('custom-alert-message');
    const closeButton = document.getElementById('custom-alert-close');

    titleElement.textContent = title;
    messageElement.textContent = message;
    closeButton.textContent = 'Aceptar';

    // Aplicar clase según el tipo
    closeButton.className = 'custom-alert-button';
    if (['error', 'success', 'warning', 'info'].includes(type)) {
        closeButton.classList.add(`alert-${type}`);
    } else {
        closeButton.classList.add('alert-info');
    }

    // Mostrar el modal
    modal.style.display = 'flex';

    // Cerrar el modal al hacer clic en "Aceptar"
    closeButton.onclick = () => {
        modal.style.display = 'none';
    };

    // Cerrar el modal al hacer clic fuera del contenido
    modal.onclick = (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };

    // Cerrar con la tecla Escape
    document.addEventListener('keydown', function handler(event) {
        if (event.key === 'Escape') {
            modal.style.display = 'none';
            document.removeEventListener('keydown', handler);
        }
    });
}