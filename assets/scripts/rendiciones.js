document.addEventListener("DOMContentLoaded", function(){
    // Inician funcionalidades para cambiar de pestañas dentro del modal de rendiciones
    let currentStep = 0;
    const steps = document.querySelectorAll(".form-step");

    function showStep(index) {
        steps.forEach((step, i) => {
            step.classList.toggle("active", i === index);
        });
        currentStep = index;
    }

    function nextStep() {
        if (currentStep < steps.length - 1) {
            showStep(currentStep + 1);
        }
    }
    
    function prevStep() {
        if (currentStep > 0) {
            showStep(currentStep - 1);
        }
    }

    function updatePanelMontosRendicion(montoSolicitado, montoRendido) {
        document.getElementById("calculo-monto-solicitado").value = parseFloat(montoSolicitado).toFixed(2);
        document.getElementById("calculo-monto-rendido").value = parseFloat(montoRendido).toFixed(2);
    }

    function renderItem(item, rendido, container, idRendicion, latestEstado) {
    const isRendido = !!rendido;
    const itemContainer = document.createElement('div');
    itemContainer.className = 'container-detalle';
    itemContainer.innerHTML = `
        <div class="compras-elementos-uno">
            <div class="modal-element">
                <span class="placeholder">Tipo</span>
                <input type="text" class="rendicion-element" value="${item.type}" readonly>
            </div>
            <div class="modal-element">
                <span class="placeholder">${item.type === 'compra' ? 'Descripción' : item.type === 'transporte' ? 'Tipo de transporte' : 'Concepto'}</span>
                <input type="text" class="rendicion-element" value="${item.descripcion || item.nombre || ''}" readonly>
            </div>
            <div class="modal-element">
                <span class="placeholder">${item.type === 'compra' ? 'Motivo' : item.type === 'transporte' ? 'Motivo de viaje' : 'Motivo de viaje'}</span>
                <input type="text" class="rendicion-element" value="${item.motivo || ''}" readonly>
            </div>
            ${item.type === 'viatico' ? `
                <div class="modal-element">
                    <span class="placeholder">Días</span>
                    <input type="text" class="rendicion-element" value="${item.dias || '0'}" readonly>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Persona</span>
                    <input type="text" class="rendicion-element" value="${item.nombre_persona || 'Sin nombre'}" readonly>
                </div>
            ` : item.type === 'transporte' ? `
                <div class="modal-element">
                    <span class="placeholder">Fecha del Viaje</span>
                    <input type="text" class="rendicion-element" value="${item.fecha ? new Date(item.fecha).toLocaleDateString() : 'Sin fecha'}" readonly>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Ciudad Origen</span>
                    <input type="text" class="rendicion-element" value="${item.ciudad_origen || 'Sin origen'}" readonly>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Ciudad Destino</span>
                    <input type="text" class="rendicion-element" value="${item.ciudad_destino || 'Sin destino'}" readonly>
                </div>
            ` : ''}
            <div class="modal-element">
                <span class="placeholder">Monto Solicitado</span>
                <input type="text" class="rendicion-element" value="${item.importe || item.monto || '0.00'}" readonly>
            </div>
            <div class="modal-element">
                <span class="placeholder">Monto Rendido</span>
                <input type="text" class="rendicion-element" id="monto-rendido-${item.id}" value="${rendido.monto_rendido || '0.00'}" readonly>
            </div>
        </div>
        <div class="compras-elementos-dos">
            <div class="btn btn-default open-list"><i class="fa-solid fa-list"></i></div>
        </div>
    `;
    container.appendChild(itemContainer);

    // Añadir evento de clic para abrir el modal, usando idRendicion en lugar de data.id
    const openListBtn = itemContainer.querySelector('.open-list');
    openListBtn.addEventListener('click', () => openComprobanteModal(item, idRendicion));
}

function openComprobanteModal(item, idRendicion) {
    const persona = item.type === 'viatico' ? item.nombre_persona : 'Solicitante';
    const modal = document.createElement('div');
    modal.className = 'comprobante-modal';
    modal.innerHTML = `
        <div class="modal-content-2">
            <div class="modal-header-2">
                <h2>Comprobantes de Pago - ${persona} (Rendicion #${idRendicion})</h2>
                <button class="btn-close-modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body-2">
                <div class="modal-sections-2">
                    <div class="left-panel">
                        <button id="addComprobanteBtn" class="btn-2">Agregar Comprobante</button>
                        <ul id="comprobanteList" class="comprobante-list"></ul>
                    </div>
                    <div class="right-panel" id="formContainer"></div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);

    // Estilos CSS inline
    const style = document.createElement('style');
    style.textContent = `
        .comprobante-modal { display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content-2 { background: white; margin: 5% auto; width: 90%; max-width: 900px; display: flex; flex-direction: column; height: 80%; border-radius: 5px; overflow: hidden; }
        .modal-header-2 { padding: 10px 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
        .modal-header-2 h2 { margin: 0; font-size: 1.2em; }
        .btn-close-modal { background: none; border: none; font-size: 1.2em; cursor: pointer; }
        .modal-body-2 { flex: 1; display: flex; overflow: auto; }
        .modal-sections-2 { display: flex; flex: 1; }
        .left-panel { width: 30%; padding: 10px; border-right: 1px solid #ddd; overflow-y: auto; }
        .right-panel { width: 70%; padding: 10px; overflow-y: auto; }
        .comprobante-list { list-style: none; padding: 0; }
        .comprobante-list li { padding: 10px; border-bottom: 1px solid #ddd; cursor: pointer; }
        .comprobante-list li:hover { background: #f5f5f5; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input, .form-group select { width: 100%; padding: 5px; }
        .btn-2 { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        .btn-2:hover { background-color: #45a049; }
        #addComprobanteBtn { background-color: #008CBA; width: 100%; margin-bottom: 10px; }
        #saveComprobanteBtn { background-color: #4CAF50; margin-top: 10px; }
        #previewContainer { margin-top: 10px; border: 1px solid #ddd; padding: 10px; display: none; }
        #previewContainer img, #previewContainer iframe { max-width: 100%; height: auto; }
        @media (max-width: 600px) {
            .modal-sections { flex-direction: column; }
            .left-panel, .right-panel { width: 100%; }
        }
    `;
    document.head.appendChild(style);

    const comprobanteList = modal.querySelector('#comprobanteList');
    const formContainer = modal.querySelector('#formContainer');
    let comprobantes = []; // Inicialización como array

    function renderForm(comprobante = null) {
        console.log('Comprobante a editar:', comprobante); // Depuración
        formContainer.innerHTML = `
            <input type="file" id="fileUpload" accept=".pdf,.jpg,.jpeg,.png" style="margin-bottom: 20px;">
            <div id="previewContainer"></div>
            <div class="form-group">
                <label>Tipo de Comprobante</label>
                <select id="tipoComprobante">
                    <option value="boleta" ${comprobante ? (comprobante.tipo_comprobante === 'boleta' ? 'selected' : '') : ''}>Boleta</option>
                    <option value="factura" ${comprobante ? (comprobante.tipo_comprobante === 'factura' ? 'selected' : '') : ''}>Factura</option>
                </select>
            </div>
            <div class="form-group">
                <label>RUC del Emisor</label>
                <input type="text" id="rucEmisor" placeholder="RUC" pattern="[0-9]{11}" value="${comprobante ? comprobante.ruc_emisor : ''}">
            </div>
            <div class="form-group">
                <label>Serie y Número</label>
                <input type="text" id="serieNumero" placeholder="Ej: F001-000123" value="${comprobante ? comprobante.serie_numero : ''}">
            </div>
            <div class="form-group">
                <label>Tipo y Número de Documento del Receptor</label>
                <input type="text" id="docReceptor" placeholder="Ej: DNI 12345678" value="${comprobante ? comprobante.doc_receptor : '20600306091'}">
            </div>
            <div class="form-group">
                <label>Fecha de Emisión</label>
                <input type="date" id="fechaEmision" value="${comprobante ? comprobante.fecha_emision : ''}">
            </div>
            <div class="form-group">
                <label>Importe Total</label>
                <input type="number" id="importeTotal" step="0.01" placeholder="0.00" value="${comprobante ? comprobante.importe_total : ''}">
            </div>
            <button id="saveComprobanteBtn" class="btn-2">Guardar</button>
        `;

        // Previsualización automática
        const fileInput = formContainer.querySelector('#fileUpload');
        const previewContainer = formContainer.querySelector('#previewContainer');
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            previewContainer.innerHTML = '';
            previewContainer.style.display = 'block';
            if (file) {
                const validExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (!validExtensions.includes(fileExtension)) {
                    alert('Solo se permiten archivos PDF, JPG, JPEG o PNG.');
                    this.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                const maxSize = 1 * 1024 * 1024; // 1 MB
                if (file.size > maxSize) {
                    alert('El archivo no debe pesar más de 1 MB.');
                    this.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(event) {
                    if (fileExtension === 'pdf') {
                        const iframe = document.createElement('iframe');
                        iframe.src = event.target.result;
                        iframe.style.width = '100%';
                        iframe.style.height = '400px';
                        previewContainer.appendChild(iframe);
                    } else {
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        previewContainer.appendChild(img);
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        const saveBtn = formContainer.querySelector('#saveComprobanteBtn');
        saveBtn.addEventListener('click', () => {
            const fileInput = formContainer.querySelector('#fileUpload');
            const file = fileInput.files[0];
            const newComprobante = {
                id: comprobante ? comprobante.id : null, // Usar el id existente o null para nuevo
                id_rendicion: idRendicion,
                id_detalle: item.id,
                tipo_comprobante: document.getElementById('tipoComprobante').value,
                ruc_emisor: document.getElementById('rucEmisor').value,
                serie_numero: document.getElementById('serieNumero').value,
                doc_receptor: document.getElementById('docReceptor').value,
                fecha_emision: document.getElementById('fechaEmision').value,
                importe_total: document.getElementById('importeTotal').value,
                archivo: file ? file : (comprobante?.archivo || null)
            };
            if (comprobante) {
                // Editar existente
                updateComprobante(comprobante.id, newComprobante);
            } else {
                // Agregar nuevo
                saveComprobante(newComprobante);
            }
            renderComprobanteList();
            renderForm(); // Limpiar formulario
        });
    }

    function renderComprobanteList() {
        comprobanteList.innerHTML = '';
        if (!Array.isArray(comprobantes)) comprobantes = [];
        console.log('Comprobantes en la lista:', comprobantes);
        const uniqueComprobantes = Array.from(new Map(comprobantes.map(c => [c.id, c])).values());
        uniqueComprobantes.forEach(comprobante => {
            const li = document.createElement('li');
            const fileLink = comprobante.archivo ? `<a href="http://127.0.0.1/proy_anticipos_rendiciones/uploads/${comprobante.archivo}" target="_blank" style="margin-left: 10px; color: #007bff; text-decoration: underline;">Ver Documento</a>` : '';
            const displayText = `Elemento ${uniqueComprobantes.indexOf(comprobante) + 1} - ${comprobante.tipo_comprobante} - S/ ${parseFloat(comprobante.importe_total).toFixed(2)}`;
            li.innerHTML = `${displayText} ${fileLink}`;
            li.dataset.id = comprobante.id;
            li.addEventListener('click', () => {
                const foundComprobante = comprobantes.find(c => c.id === comprobante.id);
                renderForm(foundComprobante);
            });
            comprobanteList.appendChild(li);
        });
        updateRendidoTotal(idRendicion, item.type);
    }

    function saveComprobante(comprobante) {
    const url = `rendiciones/guardarComprobante_${item.type}`;
    const formData = new FormData();
    for (let key in comprobante) {
        if (key === 'archivo' && comprobante[key] instanceof File) {
            formData.append('archivo', comprobante[key]);
        } else {
            formData.append(key, comprobante[key] === null ? '' : comprobante[key]); // Manejar null
        }
    }
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Comprobante guardado correctamente.');
            const newComprobante = {
                id: data.id,
                id_rendicion: comprobante.id_rendicion,
                id_detalle: comprobante.id_detalle,
                tipo_comprobante: comprobante.tipo_comprobante,
                ruc_emisor: comprobante.ruc_emisor,
                serie_numero: comprobante.serie_numero,
                doc_receptor: comprobante.doc_receptor,
                fecha_emision: comprobante.fecha_emision,
                importe_total: comprobante.importe_total,
                archivo: data.archivo
            };
            if (!Array.isArray(comprobantes)) comprobantes = [];
            const existingIndex = comprobantes.findIndex(c => c.id === newComprobante.id || c.id === '0'); // Buscar por id temporal también
            if (existingIndex !== -1) {
                comprobantes[existingIndex] = newComprobante;
            } else {
                comprobantes.push(newComprobante);
            }
            console.log('Comprobantes después de guardar:', comprobantes); // Depuración
            renderComprobanteList();
            renderForm();
            const rendidoField = document.querySelector(`#completarRendicionModal .monto-rendido[data-id="${idRendicion}"]`);
            if (rendidoField) rendidoField.value = data.monto_rendido.toFixed(2);
        } else {
            alert('Error al guardar el comprobante: ' + (data.error || 'Intente de nuevo'));
        }
    })
    .catch(error => console.error('Error al guardar comprobante:', error));
}

function updateComprobante(id, comprobante) {
    const url = `rendiciones/updateComprobante_${item.type}`;
    const formData = new FormData();
    formData.append('id', id);
    for (let key in comprobante) {
        if (key === 'archivo' && comprobante[key] instanceof File) {
            formData.append('archivo', comprobante[key]);
        } else if (key === 'archivo' && !comprobante[key]) {
            const existingComprobante = comprobantes.find(c => c.id === id);
            formData.append('archivo', existingComprobante?.archivo || '');
        } else {
            formData.append(key, comprobante[key] === null ? '' : comprobante[key]);
        }
    }
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Respuesta no exitosa: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('Comprobante actualizado correctamente.');
            const index = comprobantes.findIndex(c => c.id === id);
            if (index !== -1) {
                const updatedComprobante = { ...comprobantes[index], ...comprobante, archivo: data.archivo || comprobantes[index].archivo };
                comprobantes[index] = updatedComprobante;
            }
            renderComprobanteList();
            const rendidoField = document.querySelector(`#completarRendicionModal .monto-rendido[data-id="${idRendicion}"]`);
            if (rendidoField) rendidoField.value = data.monto_rendido.toFixed(2);
        } else {
            alert('Error al actualizar el comprobante: ' + (data.error || 'Intente de nuevo'));
        }
    })
    .catch(error => console.error('Error al actualizar comprobante:', error));
}

    function updateRendidoTotal(idRendicion, tipo) {
        const url = `rendiciones/getMontoTotalRendidoByRendicion?id_rendicion=${encodeURIComponent(idRendicion)}&tipo=${tipo}`;
        fetch(url)
        .then(res => res.json())
        .then(montoRendido => {
            const rendidoField = document.querySelector(`#completarRendicionModal .monto-rendido[data-id="${idRendicion}"]`);
            if (rendidoField) rendidoField.value = montoRendido.toFixed(2);
        })
        .catch(error => console.error('Error al actualizar monto rendido:', error));
    }

    // Evento para agregar comprobante
    modal.querySelector('#addComprobanteBtn').addEventListener('click', () => renderForm());

    // Cerrar modal
    modal.querySelector('.btn-close-modal').addEventListener('click', () => {
        // console.log("Cerrando el modal");
        // console.log(modal);
        // document.body.removeChild(modal);
        const idRendicion = modal.querySelector('.modal-header-2 h2').textContent.match(/Rendicion #(\d+)/)[1];
        fetch(`rendiciones/getMontoTotalRendidoByRendicion?id_rendicion=${encodeURIComponent(idRendicion)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updatePanelMontosRendicion(
                        document.getElementById('calculo-monto-solicitado').value,
                        data.monto_total
                    );
                }
            })
            .catch(error => console.error('Error al actualizar monto rendido:', error));

        // Cerrar el modal
        document.body.removeChild(modal);
    });

    const url = `rendiciones/getComprobantesByDetalle?id_rendicion=${idRendicion}&id_detalle=${item.id}&tipo=${item.type}`;
    fetch(url)
        .then(res => res.json())
        .then(data => {
            console.log('Datos recibidos:', data);
            // Verificar si data.comprobantes es un array válido
            const serverComprobantes = Array.isArray(data.comprobantes) ? data.comprobantes : [];
            // Combinar con los comprobantes locales, priorizando los del servidor (basado en id)
            if (!Array.isArray(comprobantes)) comprobantes = [];
            const combinedComprobantes = serverComprobantes.reduce((acc, serverComp) => {
                // Si el comprobante del servidor ya existe localmente, usamos el del servidor
                const localIndex = acc.findIndex(c => c.id === serverComp.id);
                if (localIndex !== -1) {
                    acc[localIndex] = serverComp;
                } else {
                    acc.push(serverComp);
                }
                return acc;
            }, [...comprobantes]); // Comienza con los comprobantes locales
            comprobantes = combinedComprobantes;
            renderComprobanteList();
        })
        .catch(error => {
            console.error('Error al cargar comprobantes:', error);
            if (!Array.isArray(comprobantes)) comprobantes = []; // Mantener como array vacío solo si no hay datos locales
            renderComprobanteList();
        });
    }

    function handleAprobarRendicion(idRendicion) {
        showAlert({
            title: 'Confirmación',
            message: `¿Está seguro de autorizar la rendición #${idRendicion}?`,
            type: 'confirm',
            event: 'confirm'
        });

        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');

        acceptButton.onclick = async function() {
            // valores necesarios para poder aprobar una rendición
            // obtiene el estado actual de la rendición
            const resEstado = await fetch(`rendiciones/getLatestEstadoRendicion?id_rendicion=${encodeURIComponent(idRendicion)}`);
            const idUsuarioAprobador = document.getElementById("btn-aprobar-rendicion").getAttribute("data-aprobador");
            const dniSolicitante = document.getElementById("dni-responsable").value;
            const idAnticipo = document.getElementById("id-anticipo").value;
            const motivoAnticipo = document.getElementById("motivo-anticipo").value;
            const nombreResponsable = document.getElementById("rendicion-responsable").value;
            const montoSolicitado = document.getElementById("calculo-monto-solicitado").value;
            const codigoSscc = document.getElementById("cod-sscc").value;
            const montoRendidoActual = document.getElementById("calculo-monto-rendido").value;
            const estadoData = await resEstado.json();
            const latestEstado = estadoData.estado || 'Nuevo';
            if (!['Nuevo', 'Observado'].includes(latestEstado)) {
                showAlert({
                    title: 'Error',
                    message: 'No se puede aprobar una rendición que no está en estado Nuevo u Observado.',
                    type: 'error'
                });
                const modal = document.getElementById('custom-alert-modal');
                modal.style.display = 'none';
                return;
            }
            
            const resMontoRendido = await fetch(`rendiciones/getMontoTotalRendidoByRendicion?id_rendicion=${encodeURIComponent(idRendicion)}`);
            const montoRendido = await resMontoRendido.json();
            if (montoRendido<1) {
                showAlert({
                    title: 'Error',
                    message: 'Esta rendición no puede ser aprobada, debido a que presenta como monto rendido "0".',
                    type: 'error'
                });
                //const modal = document.getElementById('custom-alert-modal');
                //modal.style.display = 'none';
                return;
            }

            const formData = new FormData();
            formData.append('id_rendicion', idRendicion);
            formData.append('id_usuario', idUsuarioAprobador);
            formData.append('dni_responsable', dniSolicitante);
            formData.append('id_anticipo', idAnticipo);
            formData.append('motivo_anticipo', motivoAnticipo);
            formData.append('nombre_responsable', nombreResponsable);
            formData.append('codigo_sscc', codigoSscc)
            formData.append('monto_solicitado', montoSolicitado);
            formData.append('monto_rendido_actualmente', montoRendidoActual);

            fetch('rendiciones/aprobarRendicion', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert({
                        title: 'Acción Completada',
                        message: 'Rendición autorizada correctamente.',
                        type: 'success',
                        event: 'envio'
                    });
                    //showRendicionDetails({ id: idRendicion });
                } else {
                    showAlert({
                        title: 'Error',
                        message: 'Error al autorizar la rendición: ' + (data.error || 'Intente de nuevo'),
                        type: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error al autorizar rendición: ', error);
                showAlert({
                    title: 'Error',
                    message: 'Error al autorizar la rendición.',
                    type: 'error'
                });
            });

            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };

        cancelButton.onclick = function() {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    }

    // Función de ejemplo para cerrar rendición (ajusta según tu backend)
    function handleCerrarRendicion(idRendicion) {
        showAlert({
            title: 'Confirmación',
            message: `¿Está seguro de finalizar la rendición #${idRendicion}?`,
            type: 'confirm',
            event: 'confirm'
        });
        
        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        const modal = document.getElementById('custom-alert-modal');

        acceptButton.onclick = async function() {
            const idUsuarioCierre = document.getElementById("btn-cerrar-rendicion").getAttribute("data-contador");
            const comentario = 'Rendición finalizada'; // Comentario fijo o vacío si no lo requiere
            const idAnticipo = document.getElementById("id-anticipo").value;
            const dniSolicitante = document.getElementById("dni-responsable").value;
            const motivoAnticipo = document.getElementById("motivo-anticipo").value;
            const nombreResponsable = document.getElementById("rendicion-responsable").value;
            const montoSolicitado = document.getElementById("calculo-monto-solicitado").value;
            const codigoSscc = document.getElementById("cod-sscc").value;
            const montoRendidoActual = document.getElementById("calculo-monto-rendido").value;

            const formData = new FormData();
            formData.append('id_rendicion', idRendicion);
            formData.append('id_usuario', idUsuarioCierre);
            formData.append('comentario', comentario);
            formData.append('id_anticipo', idAnticipo);
            formData.append('dni_responsable', dniSolicitante);
            formData.append('motivo_anticipo', motivoAnticipo);
            formData.append('nombre_responsable', nombreResponsable);
            formData.append('monto_solicitado', montoSolicitado);
            formData.append('codigo_sscc', codigoSscc);
            formData.append('monto_rendido_actual', montoRendidoActual);

            fetch('rendiciones/cerrarRendicion', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert({
                        title: 'Acción Completada',
                        message: 'Rendición finalizada correctamente.',
                        type: 'success',
                        event: 'envio'
                    });
                    //showRendicionDetails({ id: idRendicion });
                } else {
                    showAlert({
                        title: 'Error',
                        message: 'Error al finalizar la rendición: ' + (data.error || 'Intente de nuevo'),
                        type: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error al finalizar rendición: ', error);
                showAlert({
                    title: 'Error',
                    message: 'Error al finalizar la rendición.',
                    type: 'error'
                });
            });

            modal.style.display = 'none';
        };

        cancelButton.onclick = function() {
            modal.style.display = 'none';
        };
    }

    // Función de ejemplo para observar rendición (ajusta según tu backend)
    function handleObservarRendicion(idRendicion) {
        showAlert({
            title: 'Confirmación',
            message: `Marcando rendición #${idRendicion} como observada. Deberá de ingresar un comentario para completar el procedimiento`,
            type: 'confirm',
            event: 'confirm-comment'
        });

        const modal = document.getElementById('custom-alert-modal');
        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');

        acceptButton.onclick = async function() {
            const idUsuarioObservador = document.getElementById("btn-observar-rendicion").getAttribute("data-contador");
            const comentario = document.getElementById('custom-alert-comentario').value.trim();
            const idAnticipo = document.getElementById("id-anticipo").value;
            const dniSolicitante = document.getElementById("dni-responsable").value;
            const motivoAnticipo = document.getElementById("motivo-anticipo").value;
            const nombreResponsable = document.getElementById("rendicion-responsable").value;
            const montoSolicitado = document.getElementById("calculo-monto-solicitado").value;
            const codigoSscc = document.getElementById("cod-sscc").value;
            const montoRendidoActual = document.getElementById("calculo-monto-rendido").value;

            // Validación: al menos 5 letras si hay comentario
            if (comentario !== 'Sin comentario' && comentario.length < 5) {
                showAlert({
                    title: 'Error',
                    message: 'Por favor, escriba la obeservación correspondiente.',
                    type: 'error'
                });
                return;
            }

            const formData = new FormData();
            formData.append('id_rendicion', idRendicion);
            formData.append('id_usuario', idUsuarioObservador);
            formData.append('comentario', comentario);
            formData.append('id_anticipo', idAnticipo);
            formData.append('dni_responsable', dniSolicitante);
            formData.append('motivo_anticipo', motivoAnticipo);
            formData.append('nombre_responsable', nombreResponsable);
            formData.append('monto_solicitado', montoSolicitado);
            formData.append('codigo_sscc', codigoSscc);
            formData.append('monto_rendido_actual', montoRendidoActual);

            fetch('rendiciones/observarRendicion', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert({
                        title: 'Acción Completada',
                        message: 'Rendición marcada como observada correctamente.',
                        type: 'success',
                        event: 'envio'
                    });
                    //showRendicionDetails({ id: idRendicion });
                } else {
                    showAlert({
                        title: 'Error',
                        message: 'Error al marcar como observada: ' + (data.error || 'Intente de nuevo'),
                        type: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error al observar rendición: ', error);
                showAlert({
                    title: 'Error',
                    message: 'Error al marcar como observada.',
                    type: 'error'
                });
            });

            modal.style.display = 'none';
        };

        cancelButton.onclick = function() {
            modal.style.display = 'none';
        };
    }

    // Función de ejemplo para cerrar rendición (ajusta según tu backend)
    function handleCorregirRendicion(idRendicion) {
        showAlert({
            title: 'Confirmación',
            message: `¿Está seguro de finalizar la corrección de la rendición #${idRendicion}?`,
            type: 'confirm',
            event: 'confirm'
        });
        
        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        const modal = document.getElementById('custom-alert-modal');

        acceptButton.onclick = async function() {
            const idUsuarioCierre = document.getElementById("btn-corregir-rendicion").getAttribute("data-usuario");
            const comentario = 'Rendición corregida'; // Comentario fijo o vacío si no lo requiere
            const idAnticipo = document.getElementById("id-anticipo").value;
            const dniSolicitante = document.getElementById("dni-responsable").value;
            const motivoAnticipo = document.getElementById("motivo-anticipo").value;
            const nombreResponsable = document.getElementById("rendicion-responsable").value;
            const montoSolicitado = document.getElementById("calculo-monto-solicitado").value;
            const codigoSscc = document.getElementById("cod-sscc").value;
            const montoRendidoActual = document.getElementById("calculo-monto-rendido").value;

            const formData = new FormData();
            formData.append('id_rendicion', idRendicion);
            formData.append('id_usuario', idUsuarioCierre);
            formData.append('comentario', comentario);
            formData.append('id_anticipo', idAnticipo);
            formData.append('dni_responsable', dniSolicitante);
            formData.append('motivo_anticipo', motivoAnticipo);
            formData.append('nombre_responsable', nombreResponsable);
            formData.append('monto_solicitado', montoSolicitado);
            formData.append('codigo_sscc', codigoSscc);
            formData.append('monto_rendido_actual', montoRendidoActual);

            fetch('rendiciones/corregirRendicion', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert({
                        title: 'Acción Completada',
                        message: 'Rendición actualizada correctamente.',
                        type: 'success',
                        event: 'envio'
                    });
                    //showRendicionDetails({ id: idRendicion });
                } else {
                    showAlert({
                        title: 'Error',
                        message: 'Error al actualizar la rendición: ' + (data.error || 'Intente de nuevo'),
                        type: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error al actualizar rendición: ', error);
                showAlert({
                    title: 'Error',
                    message: 'Error al actualizar la rendición.',
                    type: 'error'
                });
            });

            modal.style.display = 'none';
        };

        cancelButton.onclick = function() {
            modal.style.display = 'none';
        };
    }

    window.nextStep = nextStep;
    window.prevStep = prevStep;

    //inicializar first step
    showStep(currentStep);

    document.querySelectorAll(".table.table-hover tbody tr").forEach((e)=>{
        e.addEventListener("dblclick", async function(){
            const rendicionId = e.querySelector('td[data-label="Id"]').textContent;
            try{
                const res = await fetch(`rendiciones/getRendicionDetails?id_rendicion=${encodeURIComponent(rendicionId)}`);
                if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                const data = await res.json();
                console.log(data);
                // Funcionalidad que abrirá el formulario de rención
                showRendicionDetails(data);
            } catch (error){
                console.error('Error al cargar detalles de la rendicion: ', error);
                //alert("No se pudieron cargar los detalles de la rendicion");
            }
        })
    })



    /*********************************************************************here */
    /********************************Desde aquí inicia todo lo correspondiente al completado de una rendicion*/
    const completarRendicionModal = document.getElementById("completarRendicionModal");
    const completarRendicionTitle = document.getElementById("rendicion-modal-title");
    const idRendicionResponsableModal = document.getElementById("id-rendicion");
    const idAnticipoResponsableModal = document.getElementById("id-anticipo");
    const codigoSsccResponsableModal = document.getElementById("cod-sscc");
    const codigoSccResponsableModal = document.getElementById("cod-scc");
    const nombreProyectoResponsableModal = document.getElementById("nombre-proyecto");
    const motivoAnticipoResponsableModal = document.getElementById("motivo-anticipo");

    const rendicionResponsable = document.getElementById("rendicion-responsable");
    const dniResponsable = document.getElementById("dni-responsable");
    const departamentoResponsable = document.getElementById("departamento-responsable");
    const cargoResponsable = document.getElementById("cargo-responsable");

    // Contenedor para los items de detalles
    const detallesContainer = document.getElementById('detalles-compras-container') || document.createElement('div');
    if (!detallesContainer.id) {
        detallesContainer.id = 'detalles-compras-container';
        completarRendicionModal.querySelector('.modal-body').appendChild(detallesContainer);
    }
    detallesContainer.style.display = 'flex';

    async function showRendicionDetails(data) {
    currentStep = 0;
    showStep(0);
    detallesContainer.innerHTML = '';
    completarRendicionTitle.textContent = `Rendición #${data.id}`;
    completarRendicionModal.style.display = "block";
    
    idRendicionResponsableModal.value = `${data.id}`;
    idAnticipoResponsableModal.value = `${data.id_anticipo}`;
    codigoSsccResponsableModal.value = `${data.codigo_sscc}`;
    codigoSccResponsableModal.value = `${data.scc_codigo}`;
    nombreProyectoResponsableModal.value = `${data.nombre_proyecto}`;
    rendicionResponsable.value = `${data.solicitante_nombres}`;
    dniResponsable.value = `${data.dni_solicitante}`;
    motivoAnticipoResponsableModal.value = `${data.motivo_anticipo}`;
    departamentoResponsable.setAttribute("data-departamento", `${data.departamento}`);
    departamentoResponsable.value = `${data.departamento_nombre}`;
    cargoResponsable.value = `${data.cargo}`;

    // Consultar el estado más reciente
    const resEstado = await fetch(`rendiciones/getLatestEstadoRendicion?id_rendicion=${encodeURIComponent(data.id)}`);
    const estadoData = await resEstado.json();
    const latestEstado = estadoData.estado || 'Nuevo';

    // Obtener todos los detalles y montos
    try {
        const [detallesCompras, detallesViajes, detallesTransportes, montoSolicitado, montoRendido] = await Promise.all([
            fetch(`rendiciones/getDetallesComprasMenores?id_anticipo=${encodeURIComponent(data.id_anticipo)}`).then(res => res.json()),
            fetch(`rendiciones/getDetallesViajes?id_anticipo=${encodeURIComponent(data.id_anticipo)}`).then(res => res.json()),
            fetch(`rendiciones/getDetallesTransportes?id_anticipo=${encodeURIComponent(data.id_anticipo)}`).then(res => res.json()),
            fetch(`rendiciones/getMontoSolicitadoByAnticipo?id_anticipo=${encodeURIComponent(data.id_anticipo)}`).then(res => res.json()),
            fetch(`rendiciones/getMontoTotalRendidoByRendicion?id_rendicion=${encodeURIComponent(data.id)}`).then(res => res.json())
        ]);

        // Controlar visibilidad del botón "Aprobar"
        const btnAprobar = document.getElementById('btn-aprobar-rendicion');
        if (btnAprobar) {
            const isEditable = ['Nuevo', 'Observado'].includes(latestEstado);
            btnAprobar.style.display = isEditable ? 'inline-block' : 'none';
            btnAprobar.style.opacity = isEditable ? '1' : '0';
            btnAprobar.onclick = isEditable ? () => handleAprobarRendicion(data.id) : null;
        }

        // Botones "Observar" y "Cerrar"
        const btnObservar = document.getElementById("btn-observar-rendicion");
        const btnCerrar = document.getElementById("btn-cerrar-rendicion");
        if (btnObservar && btnCerrar) {
            const isEditable = ['Autorizado'].includes(latestEstado);
            btnObservar.style.display = isEditable ? 'block' : 'none';
            btnObservar.style.opacity = isEditable ? '1' : '0';
            btnCerrar.style.display = isEditable ? 'block' : 'none';
            btnCerrar.style.opacity = isEditable ? '1' : '0';
            btnObservar.onclick = isEditable ? () => handleObservarRendicion(data.id) : null;
            btnCerrar.onclick = isEditable ? () => handleCerrarRendicion(data.id) : null;
        }

        // Botones "corregir"
        const btnCorregir = document.getElementById("btn-corregir-rendicion");
        console.log(btnCorregir);
        if (btnCorregir) {
            const isEditable = ['Observado'].includes(latestEstado);
            console.log(isEditable);
            btnCorregir.style.display = isEditable ? 'block' : 'none';
            btnCorregir.style.opacity = isEditable ? '1' : '0';
            btnCorregir.onclick = isEditable ? () => handleCorregirRendicion(data.id) : null;
        }

        // Renderizar detalles
        const allDetalles = [
            ...detallesCompras.map(item => ({ ...item, type: 'compra' })),
            ...detallesViajes.map(item => ({ ...item, type: 'viatico' })),
            ...detallesTransportes.map(item => ({ ...item, type: 'transporte' }))
        ];

        // Obtener montos rendidos por detalle desde comprobantes
        const rendidosPromises = allDetalles.map(async (item) => {
            const res = await fetch(`rendiciones/getMontoTotalRendidoByDetalle?id_rendicion=${data.id}&id_detalle=${item.id}&tipo=${item.type}`);
            const result = await res.json(); // Cambiar 'data' por 'result'
            return { id: item.id, monto_rendido: result.success ? result.monto_total : 0 };
        });
        const rendidosData = await Promise.all(rendidosPromises);
        const rendidosMap = new Map(rendidosData.map(d => [d.id.toString(), d.monto_rendido]));

        updatePanelMontosRendicion(montoSolicitado, montoRendido.monto_total);

        if (allDetalles.length > 0) {
            if (detallesCompras.length > 0) {
                const comprasSection = document.createElement('div');
                comprasSection.innerHTML = '<h3>Compras Menores</h3>';
                detallesContainer.appendChild(comprasSection);
                allDetalles.filter(item => item.type === 'compra').forEach(item => {
                    const montoRendido = rendidosMap.get(item.id.toString()) || 0;
                    renderItem(item, { monto_rendido: montoRendido }, detallesContainer, data.id, latestEstado);
                });
            }
            if (detallesViajes.length > 0) {
                const viaticosSection = document.createElement('div');
                viaticosSection.innerHTML = '<h3>Viáticos</h3>';
                detallesContainer.appendChild(viaticosSection);
                allDetalles.filter(item => item.type === 'viatico').forEach(item => {
                    const montoRendido = rendidosMap.get(item.id.toString()) || 0;
                    renderItem(item, { monto_rendido: montoRendido }, detallesContainer, data.id, latestEstado);
                });
            }
            if (detallesTransportes.length > 0) {
                const transportesSection = document.createElement('div');
                transportesSection.innerHTML = '<h3>Transportes</h3>';
                detallesContainer.appendChild(transportesSection);
                allDetalles.filter(item => item.type === 'transporte').forEach(item => {
                    const montoRendido = rendidosMap.get(item.id.toString()) || 0;
                    renderItem(item, { monto_rendido: montoRendido }, detallesContainer, data.id, latestEstado);
                });
            }
        } else {
            detallesContainer.innerHTML = '<p>No hay detalles válidos.</p>';
        }
    } catch (error) {
        console.error('Error en Promise.all:', error);
        showAlert({
            title: 'Error',
            message: 'Error al cargar los detalles de la rendición.',
            type: 'error'
        });
        detallesContainer.innerHTML = '<p>No se pudieron cargar los detalles.</p>';
    }
}

    // supuestamente ya no iba
    function closeModal(modalId) {
        document.getElementById(modalId).style.display="none";
        // Limpiar al cerrar
            currentStep = 0;
            showStep(0);
            detallesContainer.innerHTML = '';
    }

    // Botones "Cerrar" modal
    document.querySelectorAll('.btn-close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modal;
            closeModal(modalId);
        });
    });

    // exportación global
    window.nextStep = nextStep;
    window.prevStep = prevStep;
    //inicializar first step
    showStep(currentStep);

    // funcionalidad de reload
    const btnRefresh = document.getElementById("btn-refresh");
    btnRefresh.addEventListener("click", ()=>{
        window.location.reload();
    })

    // función de búsqueda
    document.getElementById("input-buscar-rendicion").addEventListener("input", function() {
        const filter = this.value.toLowerCase();
        console.log(filter);

        const rows = document.querySelectorAll("#table-body tr");

        rows.forEach(row => {
            // convierte el texto de la fila en un solo string para buscar coincidencias en cualquier columna
            const rowText = row.textContent.toLowerCase();
            // muestra u oculta la fila según si coincide o no con el filtro
            row.style.display = rowText.includes(filter) ? "" : "none";
        });
    });
})