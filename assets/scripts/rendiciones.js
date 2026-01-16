document.addEventListener("DOMContentLoaded", function(){
    // Funcionalidad para mostrar ícono de carga de la página tras ingresar/ actualizar
    window.addEventListener("load", function(){
        let rendicionesContent = document.querySelector(".rendiciones-content");
        rendicionesContent.style.display = "block";
        let loadingModalSection = document.getElementById('loadingModalPage');
        loadingModalSection.style.display = "none";
    })

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
        // modificar condicion para poder cargar recursos y rendir únicamente mientras el estado sea nuevo
        const isRendido = !!rendido;
        const itemContainer = document.createElement('div');
        itemContainer.className = 'container-detalle';
        itemContainer.innerHTML = `
            ${item.nombre_persona ? `<p class='sub-title-persona'>Persona: ${item.nombre_persona}<p>` : ''}
            <div class="compras-elementos-uno">
                <div class="modal-element" style="display: none;">
                    <span class="placeholder">Tipo</span>
                    <input type="text" class="rendicion-element" value="${item.type}" readonly>
                </div>
                <div class="modal-element">
                    <span class="placeholder">${item.type === 'compra' ? 'Descripción' : item.type === 'transporte' ? 'Transporte' : 'Concepto'}</span>
                    <input type="text" class="rendicion-element" value="${item.descripcion || item.nombre || ''}" readonly>
                </div>
                <div class="modal-element">
                    <span class="placeholder">${item.type === 'compra' ? 'Motivo' : item.type === 'transporte' ? 'Motivo de viaje' : 'Motivo de viaje'}</span>
                    <input type="text" class="rendicion-element" value="${item.motivo || ''}" title="${item.motivo || ''}" readonly>
                </div>  
            </div>
            <div style='display: flex;'>
                ${item.type === 'viatico' ? `
                    <div class="modal-element">
                        <span class="placeholder">Días</span>
                        <input type="text" class="rendicion-element" value="${item.dias || '0'}" readonly>
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
                <div class="btn btn-default open-list"><i class="fa-solid fa-list"></i> Comprobantes</div>
            </div>
        `;
        container.appendChild(itemContainer);

        // Añadir evento de clic para abrir el modal, usando idRendicion en lugar de data.id
        const openListBtn = itemContainer.querySelector('.open-list');
        openListBtn.addEventListener('click', () => openComprobanteModal(item, idRendicion, latestEstado));
    }

function openComprobanteModal(item, idRendicion, latestEstado) {
    const rol = document.getElementById("user-first-info").getAttribute("data-info")

    let puedeEditar = false;

    if((latestEstado == 'Observado' || latestEstado == 'Nuevo') && (rol == 2 || rol == 3)){
        puedeEditar = true;
    }else{
        puedeEditar = false;
    }

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
                        <button id="addComprobanteBtn" class="btn-2" ${puedeEditar ? '' : 'style="display: none;"'}>Nuevo Comprobante</button>
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
        //console.log('Comprobante a editar:', comprobante); // Depuración
        formContainer.innerHTML = `
            <input type="file" id="fileUpload" accept=".pdf,.jpg,.jpeg,.png,.xls,.xlsx, .txt" ${puedeEditar ? 'style="margin-bottom: 16px;"' : 'style="display: none;"'}>
            <div id="previewContainer"></div>
            <div class="form-group">
                <label>Tipo de Comprobante</label>
                <select id="tipoComprobante" ${puedeEditar ? '' : 'disabled'}>
                    <option value="boleta" ${comprobante ? (comprobante.tipo_comprobante === 'boleta' ? 'selected' : '') : ''}>Boleta</option>
                    <option value="factura" ${comprobante ? (comprobante.tipo_comprobante === 'factura' ? 'selected' : '') : ''}>Factura</option>
                    <option value="sin comprobante" ${comprobante ? (comprobante.tipo_comprobante === 'sin comprobante' ? 'selected' : '') : ''}>Sin Comprobante</option>
                    <option value="dc movilidad" ${comprobante ? (comprobante.tipo_comprobante === 'dec. jurada movilidad' ? 'selected' : '') : ''}>Declaración jurada de movilidad</option>
                    <option value="bol aereo" ${comprobante ? (comprobante.tipo_comprobante === 'boleto aereo' ? 'selected' : '') : ''}>Boleto aéreo</option>
                </select>
            </div>
            <div class="form-group">
                <label>RUC del Emisor</label>
                <input type="text" id="rucEmisor" placeholder="RUC" pattern="[0-9]{11}" value="${comprobante ? comprobante.ruc_emisor : ''}" ${puedeEditar ? '' : 'disabled'}>
            </div>
            <div class="form-group">
                <label>Serie y Número</label>
                <input type="text" id="serieNumero" placeholder="Ej: F001-000123" value="${comprobante ? comprobante.serie_numero : ''}" ${puedeEditar ? '' : 'disabled'}>
            </div>
            <div class="form-group">
                <label>Tipo y Número de Documento del Receptor</label>
                <input type="text" id="docReceptor" placeholder="Ej: DNI 12345678" value="${comprobante ? comprobante.doc_receptor : '20600306091'}" ${puedeEditar ? '' : 'disabled'}>
            </div>
            <div class="form-group">
                <label>Fecha de Emisión</label>
                <input type="date" id="fechaEmision" value="${comprobante ? comprobante.fecha_emision : ''}" ${puedeEditar ? '' : 'disabled'}>
            </div>
            <div class="form-group">
                <label>Importe Total</label>
                <input type="number" id="importeTotal" step="0.01" placeholder="0.00" value="${comprobante ? comprobante.importe_total : ''}" ${puedeEditar ? '' : 'disabled'}>
            </div>
            <button id="saveComprobanteBtn" class="btn-2" ${puedeEditar ? '' : 'style="display: none;"'}>Guardar</button>
        `;

        // Previsualización automática
        const fileInput = formContainer.querySelector('#fileUpload');
        const previewContainer = formContainer.querySelector('#previewContainer');
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            previewContainer.innerHTML = '';
            previewContainer.style.display = 'block';
            if (file) {
                //const validExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                const validExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'xls', 'xlsx', 'txt'];
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
                    } else if (['jpg','jpeg','png'].includes(fileExtension)) {
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.style.maxWidth = '100%';
                        previewContainer.appendChild(img);
                    } else if (['xls','xlsx'].includes(fileExtension)) {
                        const message = document.createElement('p');
                        message.textContent = `Archivo Excel cargado: ${file.name}`;
                        previewContainer.appendChild(message);
                    } else if (fileExtension === 'txt') {
                        /*const pre = document.createElement('pre');
                        pre.textContent = event.target.result;
                        pre.style.maxHeight = '200px';
                        pre.style.overflowY = 'auto';
                        previewContainer.appendChild(pre);*/
                        const message = document.createElement('p');
                        message.textContent = `Archivo cargado: ${file.name}`;
                        previewContainer.appendChild(message);
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

            //console.log(newComprobante);
            
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
        //console.log('Comprobantes en la lista:', comprobantes);
        const uniqueComprobantes = Array.from(new Map(comprobantes.map(c => [c.id, c])).values());
        uniqueComprobantes.forEach(comprobante => {
            const li = document.createElement('li');
            const fileLink = comprobante.archivo ? `<a href="http://127.0.0.1/proy_anticipos_rendiciones/uploads/${comprobante.archivo}" target="_blank" style="margin-left: 10px; color: #007bff; text-decoration: underline;">Ver Documento</a>` : '';
            //console.log(comprobante);
            const displayText = `Elemento ${uniqueComprobantes.indexOf(comprobante) + 1} - ${comprobante.tipo_comprobante} - S/ ${parseFloat(comprobante.importe_total).toFixed(2)}`;
            li.innerHTML = `${displayText} ${fileLink} - <span data-id=${comprobante.id} class="btn-remove-comprobante"><i class="fa-regular fa-trash-can"></i></span>`;

            li.querySelector('span[data-id]').addEventListener('click', async function(e) {
                e.stopPropagation(); // Evitar que se abra el formulario al clickear basura

                const comprobanteId = this.getAttribute('data-id');
                const tipo = item.type; // 'compra', 'transporte', 'viatico' — viene del item que se pasó a openComprobanteModal
                console.log(comprobanteId);
                console.log(tipo);

                // Confirmación
                if (!confirm(`¿Estás seguro de eliminar este comprobante? Esta acción no se puede deshacer.`)) {
                    return;
                }

                try {
                    const response = await fetch(`rendiciones/eliminarComprobante`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: comprobanteId,
                            tipo: tipo,          // 'compra', 'transporte', 'viatico'
                            id_rendicion: idRendicion,
                            id_detalle: item.id  // id del detalle (compra, viaje, transporte)
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Eliminar de la lista local
                        comprobantes = comprobantes.filter(c => c.id !== parseInt(comprobanteId));
                        renderComprobanteList();

                        // Actualizar monto rendido del detalle y total de rendición
                        updateRendidoTotal(idRendicion, tipo);

                        showAlert({
                            title: 'Éxito',
                            message: 'Comprobante eliminado correctamente.',
                            type: 'success'
                        });
                    } else {
                        showAlert({
                            title: 'Error',
                            message: data.error || 'No se pudo eliminar el comprobante.',
                            type: 'error'
                        });
                    }
                } catch (error) {
                    console.error('Error al eliminar comprobante:', error);
                    showAlert({
                        title: 'Error',
                        message: 'Ocurrió un error al intentar eliminar.',
                        type: 'error'
                    });
                }
            });

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
            //console.log('Comprobantes después de guardar:', comprobantes); // Depuración
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
    const btnAgregarComprobante = modal.querySelector('#addComprobanteBtn');
    if(btnAgregarComprobante){
        btnAgregarComprobante.addEventListener('click', () => renderForm());
    }

    // Cerrar modal
    modal.querySelector('.btn-close-modal').addEventListener('click', () => {
        // console.log("Cerrando el modal");
        // console.log(modal);
        // document.body.removeChild(modal);

        // variable que será utilizada para actualizar el monto total que se estaría rindiendo
        const idRendicion = modal.querySelector('.modal-header-2 h2').textContent.match(/Rendicion #(\d+)/)[1];

        // variable que estará siendo utilizada para actualizar el monto rendido del item seleccionado
        const idDetalle = item.id;

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

        // Actualizar el monto rendido del detalle específico
        fetch(`rendiciones/getMontoTotalRendidoByDetalle?id_rendicion=${encodeURIComponent(idRendicion)}&id_detalle=${encodeURIComponent(idDetalle)}&tipo=${item.type}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const montoRendidoField = document.querySelector(`#monto-rendido-${idDetalle}`);
                    if (montoRendidoField) {
                        montoRendidoField.value = parseFloat(data.monto_total).toFixed(2);
                    }
                }
            })
            .catch(error => console.error('Error al actualizar monto rendido del detalle:', error));

        // Cerrar el modal
        document.body.removeChild(modal);
    });

    const url = `rendiciones/getComprobantesByDetalle?id_rendicion=${idRendicion}&id_detalle=${item.id}&tipo=${item.type}`;
    fetch(url)
        .then(res => res.json())
        .then(data => {
            //console.log('Datos recibidos:', data);
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
        const modal = document.getElementById('custom-alert-modal');

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
            if (!['Completado'].includes(latestEstado)) {
                showAlert({
                    title: 'Error',
                    message: 'No se puede aprobar una rendición que no está en estado Nuevo u Observado.',
                    type: 'error'
                });
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

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            try{
                const response = await  fetch('rendiciones/aprobarRendicion', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
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
            } catch (error){
                console.error('Error al autorizar rendición: ', error);
                showAlert({
                    title: 'Error',
                    message: 'Error al autorizar la rendición.',
                    type: 'error'
                });
            } finally {
                loadingModal.style.display = 'none';
                modal.style.display = 'none';
            }  
        };

        cancelButton.onclick = function() {
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

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            try {
                const response = await fetch('rendiciones/cerrarRendicion', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
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
            } catch (error){
                console.error('Error al finalizar rendición: ', error);
                showAlert({
                    title: 'Error',
                    message: 'Error al finalizar la rendición.',
                    type: 'error'
                });
            } finally {
                loadingModal.style.display = 'none';
                modal.style.display = 'none';
            }
        };

        cancelButton.onclick = function() {
            modal.style.display = 'none';
        };
    }

    // Función de ejemplo para observar rendición
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
            if (comentario !== 'Sin comentario' && comentario.length < 6) {
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

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            try{
                const response = await  fetch('rendiciones/observarRendicion', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
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
            } catch (error){
                console.error('Error al observar rendición: ', error);
                showAlert({
                    title: 'Error',
                    message: 'Error al marcar como observada.',
                    type: 'error'
                });
            } finally {
                loadingModal.style.display = 'none';
                modal.style.display = 'none';
            }
        };

        cancelButton.onclick = function() {
            modal.style.display = 'none';
        };

        // 🔹 Deshabilitar el botón por defecto
        acceptButton.disabled = true;
        acceptButton.style.opacity = "0.5"; // efecto visual
        acceptButton.style.cursor = "not-allowed";

        // 🔹 Activar/desactivar según la longitud del comentario
        const comentarioInput = document.getElementById('custom-alert-comentario');
        comentarioInput.addEventListener("input", function() {
            if (this.value.trim().length >= 6) {
                acceptButton.disabled = false;
                acceptButton.style.opacity = "1";
                acceptButton.style.cursor = "pointer";
            } else {
                acceptButton.disabled = true;
                acceptButton.style.opacity = "0.5";
                acceptButton.style.cursor = "not-allowed";
            }
        });
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
            const comentario = 'Rendición corregida';
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

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            try{
                const response = await fetch('rendiciones/corregirRendicion', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
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
            } catch (error){
                console.error('Error al actualizar rendición: ', error);
                showAlert({
                    title: 'Error',
                    message: 'Error al actualizar la rendición.',
                    type: 'error'
                });
            } finally {
                loadingModal.style.display = 'none';
                modal.style.display = 'none';
            }
        };

        cancelButton.onclick = function() {
            modal.style.display = 'none';
        };
    }

    // Función de ejemplo para cerrar rendición (ajusta según tu backend)
    function handleCompletarRendicion(idRendicion) {
        showAlert({
            title: 'Confirmación',
            message: `¿Está seguro de marcar su rendición como completada #${idRendicion}?`,
            type: 'confirm',
            event: 'confirm'
        });
        
        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        const modal = document.getElementById('custom-alert-modal');

        acceptButton.onclick = async function() {
            const idUsuarioCompletando = document.getElementById("btn-completar-rendicion").getAttribute("data-usuario");
            const comentario = 'Rendición completada por el usuario';
            const idAnticipo = document.getElementById("id-anticipo").value;
            const dniSolicitante = document.getElementById("dni-responsable").value;
            const motivoAnticipo = document.getElementById("motivo-anticipo").value;
            const nombreResponsable = document.getElementById("rendicion-responsable").value;
            const montoSolicitado = document.getElementById("calculo-monto-solicitado").value;
            const codigoSscc = document.getElementById("cod-sscc").value;
            const montoRendidoActual = document.getElementById("calculo-monto-rendido").value;

            const formData = new FormData();
            formData.append('id_rendicion', idRendicion);
            formData.append('id_usuario', idUsuarioCompletando);
            formData.append('comentario', comentario);
            formData.append('id_anticipo', idAnticipo);
            formData.append('dni_responsable', dniSolicitante);
            formData.append('motivo_anticipo', motivoAnticipo);
            formData.append('nombre_responsable', nombreResponsable);
            formData.append('monto_solicitado', montoSolicitado);
            formData.append('codigo_sscc', codigoSscc);
            formData.append('monto_rendido_actual', montoRendidoActual);

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            try{
                const response = await fetch('rendiciones/completarRendicion', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    showAlert({
                        title: 'Acción Completada',
                        message: 'Rendición ha sido marcada como completada correctamente.',
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
            } catch (error){
                console.error('Error al actualizar rendición: ', error);
                showAlert({
                    title: 'Error',
                    message: 'Error al actualizar la rendición.',
                    type: 'error'
                });
            } finally {
                loadingModal.style.display = 'none';
                modal.style.display = 'none';
            }
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
                //console.log(data);
                // Funcionalidad que abrirá el formulario de rención
                showRendicionDetails(data);
            } catch (error){
                console.error('Error al cargar detalles de la rendicion: ', error);
                //alert("No se pudieron cargar los detalles de la rendicion");
            }
        })
    })



    /********************************************************************* */
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
                const isEditable = ['Completado'].includes(latestEstado);
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
            if (btnCorregir) {
                const isEditable = ['Observado'].includes(latestEstado);
                //console.log(isEditable);
                btnCorregir.style.display = isEditable ? 'block' : 'none';
                btnCorregir.style.opacity = isEditable ? '1' : '0';
                btnCorregir.onclick = isEditable ? () => handleCorregirRendicion(data.id) : null;
            }

            // Botones "completar"
            const btnCompletado = document.getElementById("btn-completar-rendicion");
            if (btnCompletado){
                const isEditable = ['Nuevo'].includes(latestEstado);
                btnCompletado.style.display = isEditable ? 'inline-block' : 'none';
                btnCompletado.style.opacity = isEditable ? '1' : '0';
                btnCompletado.onclick = isEditable ? () => handleCompletarRendicion(data.id) : null;
            }

            // Renderizar detalles
            const allDetalles = [
                ...detallesCompras.map(item => ({ ...item, type: 'compra' })),
                ...detallesViajes.map(item => ({ ...item, type: 'viatico' })),
                ...detallesTransportes.map(item => ({ ...item, type: 'transporte' }))
            ];

            //console.log(allDetalles);

            // Obtener montos rendidos por detalle desde comprobantes
            const rendidosPromises = allDetalles.map(async (item) => {
                const res = await fetch(`rendiciones/getMontoTotalRendidoByDetalle?id_rendicion=${data.id}&id_detalle=${item.id}&tipo=${item.type}`);
                const result = await res.json(); // Cambiar 'data' por 'result'

                let montoToRender = 0;
                if(result.success){
                    // console.log(result.monto_total)
                    montoToRender = result.monto_total;
                }

                return { id: item.id, monto_rendido: montoToRender };
            });
            const rendidosData = await Promise.all(rendidosPromises);
            const rendidosMap = new Map(rendidosData.map(d => [d.id.toString(), d.monto_rendido]));
            //console.log(rendidosData);
            //console.log(rendidosMap);

            updatePanelMontosRendicion(montoSolicitado, montoRendido.monto_total);// here aqui

            if (allDetalles.length > 0) {
                if (detallesCompras.length > 0) {
                    const comprasSection = document.createElement('div');
                    comprasSection.innerHTML = `
                        <h3>Compras Menores</h3>
                        <hr>
                        `;
                    detallesContainer.appendChild(comprasSection);
                    allDetalles.filter(item => item.type === 'compra').forEach(item => {
                        const montoRendido = rendidosMap.get(item.id.toString()) || 0;
                        renderItem(item, { monto_rendido: montoRendido }, detallesContainer, data.id, latestEstado);
                    });
                }
                if (detallesViajes.length > 0) {
                    const viaticosSection = document.createElement('div');
                    viaticosSection.innerHTML = `
                        <h3>Viáticos</h3>
                        <hr>
                        `;
                    detallesContainer.appendChild(viaticosSection);
                    allDetalles.filter(item => item.type === 'viatico').forEach(item => {
                        const montoRendido = rendidosMap.get(item.id.toString()) || 0;
                        renderItem(item, { monto_rendido: montoRendido }, detallesContainer, data.id, latestEstado);
                    });
                }
                if (detallesTransportes.length > 0) {
                    const transportesSection = document.createElement('div');
                    transportesSection.innerHTML = `
                        <h3>Transportes Provinciales</h3>
                        <hr>
                        `;
                    detallesContainer.appendChild(transportesSection);
                    allDetalles.filter(item => item.type === 'transporte').forEach(item => {
                        const montoRendido = rendidosMap.get(item.id.toString()) || 0;
                        renderItem(item, { monto_rendido: montoRendido }, detallesContainer, data.id, latestEstado);
                    });
                }
                /*const rol = document.getElementById("user-first-info").getAttribute("data-info");
                if(rol==2 || rol==3){
                    console.log(" ");
                }else{
                    //aqui
                    document.querySelectorAll(".compras-elementos-dos").forEach((e)=>{
                        e.style.display = "none";
                    })
                }*/
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

        const containerDescarga = document.getElementById('container-descarga');
        //console.log(containerDescarga);
        if (containerDescarga) {
            containerDescarga.innerHTML = `
                <button type="button" class="btn btn-descargar-rendicion descargar-rendicion" data-id="${data.id}" data-user="${data.solicitante_nombres.replace(/ /g, '_')}" title="Descargar detalles de rendición.">
                    Detalles <i class="fa-solid fa-download"></i>
                </button>
            `;
        }

        // Manejar el evento de descarga
        document.querySelector('.descargar-rendicion')?.addEventListener('click', async () => {
            if (!window.XLSX) {
                console.error('No se pudo descargar el documento Excel.');
                return;
            }

            const rendicionId = data.id;
            const userName = data.solicitante_nombres.replace(/ /g, '_');
            const now = new Date();
            const dateStr = `${now.getDate().toString().padStart(2, '0')}${String(now.getMonth() + 1).padStart(2, '0')}${now.getFullYear()}_${now.getHours().toString().padStart(2, '0')}${now.getMinutes().toString().padStart(2, '0')}`;
            const fileName = `${rendicionId}-${userName}-${dateStr}.xlsx`;
            const baseUrl = 'http://localhost/proy_anticipos_rendiciones/uploads/';

            let rendicionData = [], viajesData = [], comprasData = [], transportesData = [];
            try {
                const response = await fetch(`rendiciones/getRendicionCompleta?rendicion_id=${rendicionId}`);
                const result = await response.json();
                if (result.success) {
                    // Hoja 1: Rendición Detalles
                    if (result.data.rendicion) {
                        rendicionData = [{
                            id_Rendicion: result.data.rendicion.id,
                            id_Anticipo: result.data.rendicion.id_anticipo,
                            id_Usuario: result.data.rendicion.id_usuario,
                            Fecha_Rendicion: result.data.rendicion.fecha_rendicion,
                            id_Cat_Documento: result.data.rendicion.id_cat_documento,
                            Monto_Solicitado: parseFloat(result.data.rendicion.monto_solicitado).toFixed(2) || 0,
                            Monto_Rendido: parseFloat(result.data.rendicion.monto_rendido).toFixed(2) || 0,
                            Responsable: result.data.rendicion.responsable,
                            Departamento: result.data.rendicion.departamento,
                            Sscc: result.data.rendicion.sscc
                        }];
                    }

                    // Hoja 2: Detalles de Viajes
                    if (Array.isArray(result.data.viajes)) {
                        viajesData = result.data.viajes.map(item => {
                            const baseData = {
                                Detalle_ID: item.detalle_id,
                                id_Viaje_Persona: item.id_viaje_persona,
                                Nombre_Concepto: item.nombre_concepto || '',
                                Dias: parseInt(item.dias) || 0,
                                Monto_Viaje: parseFloat(item.monto) || 0, // Forzar numérico
                                Moneda: item.moneda || '',
                                Doc_Identidad: item.doc_identidad || '',
                                Nombre_Persona: item.nombre_persona || ''
                            };
                            const archivoUrl = item.nombre_archivo ? `${baseUrl}${item.archivo || ''}` : '';
                            if (item.comprobante_id) {
                                return {
                                    ...baseData,
                                    Tipo_Comprobante: item.tipo_comprobante || '',
                                    RUC_Emisor: item.ruc_emisor || '',
                                    Serie_Numero: item.serie_numero || '',
                                    Doc_Receptor: item.doc_receptor || '',
                                    Fecha_Emision: item.fecha_emision || '',
                                    Importe_Total: parseFloat(item.importe_total) || 0, // Forzar numérico
                                    Nombre_Archivo: item.nombre_archivo || '',
                                    Archivo_URL: archivoUrl
                                };
                            }
                            return {
                                ...baseData,
                                Nombre_Archivo: item.nombre_archivo || '',
                                Archivo_URL: archivoUrl
                            };
                        });
                    }

                    // Hoja 3: Comprobantes Compras
                    if (Array.isArray(result.data.compras)) {
                        comprasData = result.data.compras.map(item => {
                            const baseData = {
                                Detalle_ID: item.detalle_id,
                                Descripcion: item.descripcion || '',
                                Motivo: item.motivo || '',
                                Moneda: item.moneda || '',
                                Importe: parseFloat(item.importe) || 0 // Forzar numérico
                            };
                            const archivoUrl = item.nombre_archivo ? `${baseUrl}${item.archivo || ''}` : '';
                            if (item.comprobante_id) {
                                return {
                                    ...baseData,
                                    Tipo_Comprobante: item.tipo_comprobante || '',
                                    RUC_Emisor: item.ruc_emisor || '',
                                    Serie_Numero: item.serie_numero || '',
                                    Doc_Receptor: item.doc_receptor || '',
                                    Fecha_Emision: item.fecha_emision || '',
                                    Importe_Total: parseFloat(item.importe_total) || 0, // Forzar numérico
                                    Nombre_Archivo: item.nombre_archivo || '',
                                    Archivo_URL: archivoUrl
                                };
                            }
                            return {
                                ...baseData,
                                Nombre_Archivo: item.nombre_archivo || '',
                                Archivo_URL: archivoUrl
                            };
                        });
                    }

                    // Hoja 4: Comprobantes Transportes
                    if (Array.isArray(result.data.transportes)) {
                        transportesData = result.data.transportes.map(item => {
                            const baseData = {
                                Detalle_ID: item.detalle_id,
                                id_Viaje_Persona: item.id_viaje_persona,
                                Tipo_Transporte: item.tipo_transporte || '',
                                Ciudad_Origen: item.ciudad_origen || '',
                                Ciudad_Destino: item.ciudad_destino || '',
                                Fecha: item.fecha || '',
                                Monto: parseFloat(item.monto) || 0, // Forzar numérico
                                Moneda: item.moneda || '',
                                Doc_Identidad: item.doc_identidad || '',
                                Nombre_Persona: item.nombre_persona || ''
                            };
                            const archivoUrl = item.nombre_archivo ? `${baseUrl}${item.archivo || ''}` : '';
                            if (item.comprobante_id) {
                                return {
                                    ...baseData,
                                    Tipo_Comprobante: item.tipo_comprobante || '',
                                    RUC_Emisor: item.ruc_emisor || '',
                                    Serie_Numero: item.serie_numero || '',
                                    Doc_Receptor: item.doc_receptor || '',
                                    Fecha_Emision: item.fecha_emision || '',
                                    Importe_Total: parseFloat(item.importe_total) || 0, // Forzar numérico
                                    Nombre_Archivo: item.nombre_archivo || '',
                                    Archivo_URL: archivoUrl
                                };
                            }
                            return {
                                ...baseData,
                                Nombre_Archivo: item.nombre_archivo || '',
                                Archivo_URL: archivoUrl
                            };
                        });
                    }
                }
            } catch (error) {
                console.error('Error fetching data:', error);
                alert('Warning: Some data could not be loaded. The file may be incomplete.');
            }

            // Generate Excel using SheetJS
            const { utils, writeFile } = window.XLSX;
            const wb = utils.book_new();

            // Sheet 1: Rendición Detalles
            if (rendicionData.length > 0) {
                const rendicionWs = utils.json_to_sheet(rendicionData, { header: ['id_Rendicion', 'id_Anticipo', 'id_Usuario', 'Fecha_Rendicion', 'id_Cat_Documento', 'Monto_Solicitado', 'Monto_Rendido', 'Responsable', 'Departamento', 'Sscc'] });
                utils.book_append_sheet(wb, rendicionWs, 'Rendición Detalles');
            }

            // Sheet 2: Detalles de Viajes
            if (viajesData.length > 0) {
                const viajesWs = utils.json_to_sheet(viajesData, { 
                    header: [
                        'Detalle_ID', 'id_Viaje_Persona', 'Nombre_Concepto', 'Dias', 'Monto_Viaje', 
                        'Moneda', 'Doc_Identidad', 'Nombre_Persona', 'Tipo_Comprobante', 
                        'RUC_Emisor', 'Serie_Numero', 'Doc_Receptor', 'Fecha_Emision', 
                        'Importe_Total', 'Nombre_Archivo', 'Archivo_URL'
                    ]
                });
                utils.book_append_sheet(wb, viajesWs, 'Detalles de Viajes');
            }

            // Sheet 3: Comprobantes Compras
            if (comprasData.length > 0) {
                const comprasWs = utils.json_to_sheet(comprasData, { 
                    header: [
                        'Detalle_ID', 'Descripcion', 'Motivo', 'Moneda', 'Importe', 
                        'Tipo_Comprobante', 'RUC_Emisor', 'Serie_Numero', 'Doc_Receptor', 
                        'Fecha_Emision', 'Importe_Total', 'Nombre_Archivo', 'Archivo_URL'
                    ]
                });
                utils.book_append_sheet(wb, comprasWs, 'Comprobantes Compras');
            }

            // Sheet 4: Comprobantes Transportes
            if (transportesData.length > 0) {
                const transportesWs = utils.json_to_sheet(transportesData, { 
                    header: [
                        'Detalle_ID', 'id_Viaje_Persona', 'Tipo_Transporte', 'Ciudad_Origen', 
                        'Ciudad_Destino', 'Fecha', 'Monto', 'Moneda', 'Doc_Identidad', 
                        'Nombre_Persona', 'Tipo_Comprobante', 'RUC_Emisor', 'Serie_Numero', 
                        'Doc_Receptor', 'Fecha_Emision', 'Importe_Total', 'Nombre_Archivo', 'Archivo_URL'
                    ]
                });
                utils.book_append_sheet(wb, transportesWs, 'Comprobantes Transportes');
            }

            // Write and trigger download
            writeFile(wb, fileName);
        });
    }

    // supuestamente ya no iba
    function closeModal(modalId) {
        document.getElementById(modalId).style.display="none";
        // Limpiar al cerrar
            currentStep = 0;
            showStep(0);
            detallesContainer.innerHTML = '';
    }

    // Botones "Cerrar" modal /
    document.querySelectorAll('.btn-close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modal;
            //console.log(modalId);
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
        //console.log(filter);

        const rows = document.querySelectorAll("#table-body tr");

        rows.forEach(row => {
            // convierte el texto de la fila en un solo string para buscar coincidencias en cualquier columna
            const rowText = row.textContent.toLowerCase();
            // muestra u oculta la fila según si coincide o no con el filtro
            row.style.display = rowText.includes(filter) ? "" : "none";
        });
    });

    /** inicia funcionalidad para ordenar tras presionar en fecha maxima de rendición */
    const th = document.getElementById("ordenar-rendicion");
    const icon = document.getElementById("flecha-rendicion");
    const tbody = document.getElementById("table-body");

    let asc = true;  // primer clic = ordenar ASC

    th.style.cursor = "pointer";

    th.addEventListener("click", function () {

        const rows = Array.from(tbody.querySelectorAll("tr"));

        rows.sort((a, b) => {
            const valA = a.cells[7].textContent.trim();
            const valB = b.cells[7].textContent.trim();

            const fechaA = valA === "N/A" || valA === "" ? 0 : new Date(valA).getTime();
            const fechaB = valB === "N/A" || valB === "" ? 0 : new Date(valB).getTime();

            return asc ? fechaA - fechaB : fechaB - fechaA;
        });

        // actualizar tabla
        tbody.innerHTML = "";
        rows.forEach(r => tbody.appendChild(r));

        // Cambiar icono Font Awesome
        if (asc) {
            icon.className = "fa-solid fa-sort-up ms-1";    // Ascendente
        } else {
            icon.className = "fa-solid fa-sort-down ms-1";  // Descendente
        }

        asc = !asc; // alternar modo
    });
    /**finaliza funcionalidad par ordenar por fecha max de rendición */

    /** Ordenamiento por Fecha de Inicio */
    const thInicio = document.getElementById("ordenar-inicio");
    const iconInicio = document.getElementById("flecha-inicio");
    const tbodyInicio = document.getElementById("table-body");

    let ascInicio = true;

    thInicio.style.cursor = "pointer";

    thInicio.addEventListener("click", function () {

        const rows = Array.from(tbodyInicio.querySelectorAll("tr"));

        rows.sort((a, b) => {
            const valA = a.cells[6].textContent.trim();
            const valB = b.cells[6].textContent.trim();

            const fechaA = valA === "N/A" || valA === "" ? 0 : new Date(valA).getTime();
            const fechaB = valB === "N/A" || valB === "" ? 0 : new Date(valB).getTime();

            return ascInicio ? fechaA - fechaB : fechaB - fechaA;
        });

        tbodyInicio.innerHTML = "";
        rows.forEach(r => tbodyInicio.appendChild(r));

        // Cambiar icono
        iconInicio.className = ascInicio
            ? "fa-solid fa-sort-up ms-1"
            : "fa-solid fa-sort-down ms-1";

        ascInicio = !ascInicio;
    });

    /**finaliza funcionalidad para ordenar por fecha de inicio */


    /**Inicia ordenamiento por estado */
    const estadoOrden = {
        "Nuevo": 1,
        "Observado": 2,
        "Autorizado": 3,
        "Completado": 4,
        "Rendido": 5
    };

    const th1 = document.getElementById("ordenar-estado");
    const icon1 = document.getElementById("flecha-estado");
    const tbody1 = document.getElementById("table-body");

    let asc1 = true;

    th1.style.cursor = "pointer";

    th1.addEventListener("click", function () {

        const rows1 = Array.from(tbody1.querySelectorAll("tr"));

        rows1.sort((a, b) => {
            const estadoA1 = a.cells[10].innerText.trim();
            const estadoB1 = b.cells[10].innerText.trim();

            const orderA1 = estadoOrden[estadoA1] || 999;
            const orderB1 = estadoOrden[estadoB1] || 999;

            return asc1 ? orderA1 - orderB1 : orderB1 - orderA1;
        });

        tbody1.innerHTML = "";
        rows1.forEach(r => tbody1.appendChild(r));

        if (asc1) {
            icon1.className = "fa-solid fa-sort-up ms-1";
        } else {
            icon1.className = "fa-solid fa-sort-down ms-1";
        }

        asc1 = !asc1;
    });
    /**Finaliza ordenamiento por estado */

    /**Funcionalidad para aplicar filtros */
    document.getElementById("btn-aplicar").addEventListener("click", function(e) {
        e.preventDefault();

        const estado = document.getElementById("filtro-estado").value.toLowerCase();
        const anio = document.getElementById("filtro-anio").value;

        const inicioDesde = document.getElementById("filtro-inicio-desde").value;
        const inicioHasta = document.getElementById("filtro-inicio-hasta").value;

        const rendicionDesde = document.getElementById("filtro-rendicion-desde").value;
        const rendicionHasta = document.getElementById("filtro-rendicion-hasta").value;

        const rows = document.querySelectorAll("#table-body tr");

        rows.forEach(row => {

            const colEstado = row.cells[10].innerText.trim().toLowerCase();
            const colInicio = row.cells[6].innerText.trim();
            const colRendicion = row.cells[7].innerText.trim();

            const fechaInicio = colInicio ? new Date(colInicio) : null;
            const fechaRendicion = colRendicion ? new Date(colRendicion) : null;

            let mostrar = true;

            // Filtro estado
            if (estado && colEstado !== estado) {
                mostrar = false;
            }

            // Filtro año (sobre fecha rendicion)
            if (anio && fechaRendicion && fechaRendicion.getFullYear().toString() !== anio) {
                mostrar = false;
            }

            // Rango fecha inicio
            if (inicioDesde && fechaInicio && fechaInicio < new Date(inicioDesde)) {
                mostrar = false;
            }
            if (inicioHasta && fechaInicio && fechaInicio > new Date(inicioHasta)) {
                mostrar = false;
            }

            // Rango fecha rendición
            if (rendicionDesde && fechaRendicion && fechaRendicion < new Date(rendicionDesde)) {
                mostrar = false;
            }
            if (rendicionHasta && fechaRendicion && fechaRendicion > new Date(rendicionHasta)) {
                mostrar = false;
            }

            row.style.display = mostrar ? "" : "none";
        });

        let panel = document.getElementById("filterPanel");
        panel.classList.toggle("active");
        // console.log("Filtrando");
    });

    // botón para limpiar filtros
    document.getElementById("limpiar-filtros").addEventListener("click", function(e) {
        e.preventDefault();
        document.querySelectorAll(".filtersForm select, .filtersForm input")
            .forEach(el => el.value = "");

        document.querySelectorAll("#table-body tr").forEach(row => {
            row.style.display = "";
        });
    });

    // Funcionalidad para mostrar el panel de filtros
    let bloqueado = false; 
    document.getElementById("toggleFilters").addEventListener("click", function() {

        if (bloqueado) return; // ignora clics repetidos

        bloqueado = true; // se bloquea la ejecución para clics

        let panel = document.getElementById("filterPanel");

        panel.classList.toggle("active");

        setTimeout(() => {
            bloqueado = false; // se vuelve a habilitar para otro clic
        }, 600);
    });


    // texto de ayuda para filtros
    const tooltip = document.getElementById("tooltip-filtros");
    // funcionalidad para mostrar el texto de ayuda
    function mostrarTooltip() {
        tooltip.style.opacity = "1";

        // Ocultar después de 2 segundos
        setTimeout(() => {
            tooltip.style.opacity = "0";
        }, 2000);
    }
    // se ejecuta tras agregar la página
    mostrarTooltip();

    // mostrar el texto en caso de hover
    document.getElementById("toggleFilters").addEventListener("mouseenter", mostrarTooltip);

})