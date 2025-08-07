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
                    <input type="number" step="0.01" class="rendicion-element monto-rendido" value="${isRendido ? rendido.monto_rendido : '0.00'}" required ${['Nuevo', 'Observado'].includes(latestEstado) ? '' : 'disabled'}>
                </div>
            </div>
            <div class="compras-elementos-dos">
                <div class="modal-element">
                    <span class="placeholder">Fecha de Rendición</span>
                    <input type="date" class="rendicion-element fecha-rendicion" value="${isRendido ? rendido.fecha : new Date().toISOString().split('T')[0]}" ${['Nuevo', 'Observado'].includes(latestEstado) ? '' : 'disabled'} required>
                </div>
                <div class="modal-element">
                    <input type="file" class="file-input" style="display: none;" ${['Nuevo', 'Observado'].includes(latestEstado) ? '' : 'disabled'}>
                    <div class="btn btn-adjuntar"><i class="fa-solid fa-file-invoice"></i> Adjuntar</div>
                </div>
                <p class="enlace-factura">
                    <a ${isRendido && rendido.archivo_adjunto ? 'href=#' : ''} class="archivo-nombre" target="_blank">${isRendido && rendido.archivo_adjunto ? rendido.archivo_adjunto : 'Sin archivo'}</a>
                </p>
            </div>
            <div class="modal-footer-item">
                <button class="btn btn-default btn-guardar-item" ${['Nuevo', 'Observado'].includes(latestEstado) ? '' : 'disabled'}>Guardar ítem</button>
            </div>
        `;
        container.appendChild(itemContainer);

        const fileInput = itemContainer.querySelector('.file-input');
        const adjuntarBtn = itemContainer.querySelector('.btn-adjuntar');
        const archivoNombre = itemContainer.querySelector('.archivo-nombre');
        const guardarItemBtn = itemContainer.querySelector('.btn-guardar-item');

        adjuntarBtn.addEventListener('click', () => fileInput.click());
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const extensionesValidas = ['pdf', 'jpg', 'jpeg', 'png'];
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (!extensionesValidas.includes(fileExtension)) {
                    showAlert({
                        title: 'Error',
                        message: 'Solo se permiten archivos PDF, JPG, JPEG o PNG.',
                        type: 'error'
                    });
                    fileInput.value = '';
                    archivoNombre.textContent = 'Sin archivo';
                    return;
                }
                const maxSize = 1 * 1024 * 1024; // 1 MB
                if (file.size > maxSize) {
                    showAlert({
                        title: 'Error',
                        message: 'El archivo no debe pesar más de 1 MB.',
                        type: 'error'
                    });
                    fileInput.value = '';
                    archivoNombre.textContent = 'Sin archivo';
                    return;
                }
                archivoNombre.textContent = file.name;
                archivoNombre.href = URL.createObjectURL(file);
            }
        });

        guardarItemBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (guardarItemBtn.disabled) return;
            guardarItemBtn.disabled = true;
            const detalle = {
                id: item.id,
                type: item.type,
                montoSolicitado: item.importe || item.monto,
                montoRendido: itemContainer.querySelector('.monto-rendido').value,
                fecha: itemContainer.querySelector('.fecha-rendicion').value,
                archivo: fileInput.files[0] || (isRendido && rendido.archivo_adjunto ? { name: rendido.archivo_adjunto } : undefined)
            };
            console.log(detalle);
            if (item.type === 'compra') {
                guardarItemIndividual(detalle, idRendicion, itemContainer).then(() => {
                    guardarItemBtn.disabled = false;
                    updateTotals(idRendicion);
                    guardarItemBtn.style.backgroundColor = "#4cd137";
                    guardarItemBtn.innerHTML = '<i class="fa-solid fa-check"></i> Guardar ítem';
                }).catch(error => {
                    guardarItemBtn.disabled = false;
                    console.error('Error en guardado: ', error);
                });
            } else if (item.type === 'viatico') {
                guardarItemViaje(detalle, idRendicion, itemContainer).then(() => {
                    guardarItemBtn.disabled = false;
                    updateTotals(idRendicion);
                    guardarItemBtn.style.backgroundColor = "#4cd137";
                    guardarItemBtn.innerHTML = '<i class="fa-solid fa-check"></i> Guardar ítem';
                }).catch(error => {
                    guardarItemBtn.disabled = false;
                    console.error('Error en guardado: ', error);
                });
            } else if (item.type === 'transporte') {
                guardarItemTransporte(detalle, idRendicion, itemContainer).then(() => {
                    guardarItemBtn.disabled = false;
                    updateTotals(idRendicion);
                                        guardarItemBtn.style.backgroundColor = "#4cd137";
                    guardarItemBtn.innerHTML = '<i class="fa-solid fa-check"></i> Guardar ítem';
                }).catch(error => {
                    guardarItemBtn.disabled = false;
                    console.error('Error en guardado: ', error);
                });
            }
        });

        if (isRendido && rendido.archivo_adjunto) {
            archivoNombre.href = 'uploads/' + rendido.archivo_adjunto;
        }
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

            const formData = new FormData();
            formData.append('id_rendicion', idRendicion);
            formData.append('id_usuario', idUsuarioCierre);
            formData.append('comentario', comentario);
            formData.append('id_anticipo', idAnticipo);

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

    function updateTotals(idRendicion) {
        const idAnticipo = document.getElementById("id-anticipo").value;
        Promise.all([
            fetch(`rendiciones/getMontoSolicitadoByAnticipo?id_anticipo=${encodeURIComponent(idAnticipo)}`).then(res => res.json()),
            fetch(`rendiciones/getMontoTotalRendidoByRendicion?id_rendicion=${encodeURIComponent(idRendicion)}`).then(res => res.json())
        ]).then(([montoSolicitado, montoRendido]) => {
            updatePanelMontosRendicion(montoSolicitado, montoRendido);
        }).catch(error => {
            console.error('Error al actualizar totales: ', error);
        });
    }

    function guardarItemIndividual(detalle, id_rendicion, container) {
        return new Promise((resolve, reject) => {
            showAlert({
                title: 'Confirmación',
                message: `¿Está seguro de guardar el item correspondiente a la rendición ${id_rendicion}.`,
                type: 'confirm',
                event: 'confirm'
            });

            const acceptButton = document.getElementById('custom-alert-btn-aceptar');
            const cancelButton = document.getElementById('custom-alert-btn-cancelar');

            acceptButton.onclick = function(){
                const formData = new FormData();
                formData.append('id_rendicion', id_rendicion);
                formData.append('id_detalle_compra', detalle.id); // Usar detalle.id como se ajustó antes
                formData.append('montoRendido', detalle.montoRendido);
                formData.append('fecha', detalle.fecha);
                if (detalle.archivo instanceof File) {
                    formData.append('archivo', detalle.archivo);
                } else if (detalle.archivo && typeof detalle.archivo === 'object' && detalle.archivo.name) {
                    formData.append('archivo_existente', detalle.archivo.name);
                }
                fetch('rendiciones/guardarItemRendido', { // Asegurar que devuelva la promesa
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        //alert("Ítem de compra guardado o actualizado exitosamente.");
                        showAlert({
                            title: 'Acción Completada.',
                            message: 'El item ha sido guardado correctamente',
                            type: 'success'
                        });
                        const archivoNombre = container.querySelector('.archivo-nombre');
                        if (detalle.archivo instanceof File) {
                            archivoNombre.textContent = detalle.archivo.name;
                            archivoNombre.href = URL.createObjectURL(detalle.archivo);
                        }
                        resolve(); // se resuelve la promesa en caso de exito
                    } else {
                        //alert("Error al guardar el ítem de compra: " + (data.error || 'Intente de nuevo'));
                        showAlert({
                            title: 'Error',
                            message: 'Error al guardar el ítem de compra.',
                            type: 'error'
                        });
                        reject(new Error(data.error || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error al guardar ítem de compra: ', error);
                    // alert("Error al guardar el ítem de compra");
                    reject(error);
                    showAlert({
                        title: 'Error',
                        message: 'Error al guardar el ítem de compra.',
                        type: 'error'
                    });
                });
                const modal = document.getElementById("custom-alert-modal");
                modal.style.display = "none";
            };

            cancelButton.onclick = () => {
                const modal = document.getElementById('custom-alert-modal');
                modal.style.display = 'none';
                reject(new Error('Cancelado por el usuario'));
            }
        });
    }

    function guardarItemViaje(detalle, id_rendicion, container) {
        return new Promise((resolve, reject) => {
            showAlert({
                title: 'Confirmación',
                message: `¿Está seguro de guardar este item correspondiente a la rendición ${id_rendicion}.`,
                type: 'confirm',
                event: 'confirm'
            });

            const acceptButton = document.getElementById('custom-alert-btn-aceptar');
            const cancelButton = document.getElementById('custom-alert-btn-cancelar');

            acceptButton.onclick = function(){
                const formData = new FormData();
                formData.append('id_rendicion', id_rendicion);
                formData.append('id_detalle_viaje', detalle.id);
                formData.append('montoRendido', detalle.montoRendido);
                formData.append('fecha', detalle.fecha);
                if (detalle.archivo instanceof File) {
                    formData.append('archivo', detalle.archivo);
                } else if (detalle.archivo && typeof detalle.archivo === 'object' && detalle.archivo.name) {
                    formData.append('archivo_existente', detalle.archivo.name);
                }
                fetch('rendiciones/guardarItemViaje', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        //alert("Ítem de viático guardado o actualizado exitosamente.");
                        showAlert({
                            title: 'Acción Completada.',
                            message: 'El item ha sido guardado correctamente',
                            type: 'success'
                        });
                        const archivoNombre = container.querySelector('.archivo-nombre');
                        if (detalle.archivo instanceof File) {
                            archivoNombre.textContent = detalle.archivo.name;
                            archivoNombre.href = URL.createObjectURL(detalle.archivo);
                        }
                        resolve(); // se resuelve la promesa en caso de exito
                    } else {
                        //alert("Error al guardar el ítem de viático: " + (data.error || 'Intente de nuevo'));
                        showAlert({
                            title: 'Error',
                            message: 'Error al guardar el ítem de viático.',
                            type: 'error'
                        });
                        reject(new Error(data.error || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error al guardar ítem de viático: ', error);
                    //alert("Error al guardar el ítem de viático");
                    showAlert({
                        title: 'Error',
                        message: 'Error al guardar el ítem de compra.',
                        type: 'error'
                    });
                    reject(error);
                });

                const modal = document.getElementById("custom-alert-modal");
                modal.style.display = "none";
            };

            cancelButton.onclick = () => {
                const modal = document.getElementById('custom-alert-modal');
                modal.style.display = 'none';
                reject(new Error('Cancelado por el usuario'));
            }
        });
    }

    function guardarItemTransporte(detalle, id_rendicion, container) {
        return new Promise((resolve, reject) => {
            showAlert({
                title: 'Confirmación',
                message: `¿Está seguro de que desea guardar este item correspondiente a la rendición ${id_rendicion}.`,
                type: 'confirm',
                event: 'confirm'
            });

            const acceptButton = document.getElementById('custom-alert-btn-aceptar');
            const cancelButton = document.getElementById('custom-alert-btn-cancelar');

            acceptButton.onclick = function(){
                const formData = new FormData();
                formData.append('id_rendicion', id_rendicion);
                formData.append('id_transporte_provincial', detalle.id);
                formData.append('montoRendido', detalle.montoRendido);
                formData.append('fecha', detalle.fecha);
                if (detalle.archivo instanceof File) {
                    formData.append('archivo', detalle.archivo);
                } else if (detalle.archivo && typeof detalle.archivo === 'object' && detalle.archivo.name) {
                    formData.append('archivo_existente', detalle.archivo.name);
                }
                fetch('rendiciones/guardarItemTransporte', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        //alert("Ítem de transporte guardado o actualizado exitosamente.");
                        showAlert({
                            title: 'Acción Completada.',
                            message: 'El item ha sido guardado correctamente',
                            type: 'success'
                        });
                        const archivoNombre = container.querySelector('.archivo-nombre');
                        if (detalle.archivo instanceof File) {
                            archivoNombre.textContent = detalle.archivo.name;
                            archivoNombre.href = URL.createObjectURL(detalle.archivo);
                        }
                        resolve();
                    } else {
                        //alert("Error al guardar el ítem de transporte: " + (data.error || 'Intente de nuevo'));
                        showAlert({
                            title: 'Error',
                            message: 'Error al guardar el ítem de transporte.',
                            type: 'error'
                        });
                        reject(new Error(data.error || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error al guardar ítem de transporte: ', error);
                    //alert("Error al guardar el ítem de transporte");
                    showAlert({
                        title: 'Error',
                        message: 'Error al guardar el ítem de transporte.',
                        type: 'error'
                    });
                    reject(error);
                });
                const modal = document.getElementById("custom-alert-modal");
                modal.style.display = "none";
            }
            cancelButton.onclick = () => {
                const modal = document.getElementById('custom-alert-modal');
                modal.style.display = 'none';
                reject(new Error('Cancelado por el usuario'));
            }
        });
    }

    // exportación global
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

        // Obtener todos los detalles
        try {
            const [detallesCompras, detallesViajes, detallesTransportes, detallesComprasRendidos, detallesViajesRendidos, detallesTransportesRendidos, montoSolicitado, montoRendido] = await Promise.all([
                fetch(`rendiciones/getDetallesComprasMenores?id_anticipo=${encodeURIComponent(data.id_anticipo)}`).then(res => res.json()),
                fetch(`rendiciones/getDetallesViajes?id_anticipo=${encodeURIComponent(data.id_anticipo)}`).then(res => res.json()),
                fetch(`rendiciones/getDetallesTransportes?id_anticipo=${encodeURIComponent(data.id_anticipo)}`).then(res => res.json()),
                fetch(`rendiciones/getDetallesRendidosByRendicion?id_rendicion=${encodeURIComponent(data.id)}`).then(res => res.json()),
                fetch(`rendiciones/getDetallesViajesRendidosByRendicion?id_rendicion=${encodeURIComponent(data.id)}`).then(res => res.json()),
                fetch(`rendiciones/getDetallesTransportesRendidosByRendicion?id_rendicion=${encodeURIComponent(data.id)}`).then(res => res.json()),
                fetch(`rendiciones/getMontoSolicitadoByAnticipo?id_anticipo=${encodeURIComponent(data.id_anticipo)}`).then(res => res.json()),
                fetch(`rendiciones/getMontoTotalRendidoByRendicion?id_rendicion=${encodeURIComponent(data.id)}`).then(res => res.json())
            ]);

            console.log('Detalles Compras:', detallesCompras);
            console.log('Detalles Viajes:', detallesViajes);
            console.log('Detalles Transportes:', detallesTransportes);

            // Controlar visibilidad del botón "Aprobar"
            const btnAprobar = document.getElementById('btn-aprobar-rendicion');
            //console.log(latestEstado);
            if (btnAprobar) {
                const isEditable = ['Nuevo', 'Observado'].includes(latestEstado);
                btnAprobar.style.display = isEditable ? 'inline-block' : 'none';
                btnAprobar.style.opacity = isEditable ? '1' : '0';
                btnAprobar.onclick = isEditable ? () => handleAprobarRendicion(data.id) : null;
            }

            // btn observar y btn cerrar.
            const btnObservar = document.getElementById("btn-observar-rendicion");
            const btnCerrar = document.getElementById("btn-cerrar-rendicion");
            if(btnObservar && btnCerrar){
                const isEditable = ['Autorizado'].includes(latestEstado);
                btnObservar.style.display = isEditable ? 'block' : 'none';
                btnObservar.style.opacity = isEditable ? '1' : '0';
                btnCerrar.style.display = isEditable ? 'block' : 'none';
                btnCerrar.style.opacity = isEditable ? '1' : '0';
                btnObservar.onclick = isEditable ? () => handleObservarRendicion(data.id) : null;
                btnCerrar.onclick = isEditable ? () => handleCerrarRendicion(data.id) : null;
            }

            // Renderizar detalles
            const rendidosMap = new Map([
                ...detallesComprasRendidos.map(item => [item.id_detalle_compra.toString(), { ...item, type: 'compra' }]),
                ...detallesViajesRendidos.map(item => [item.id_detalle_viaje.toString(), { ...item, type: 'viatico' }]),
                ...detallesTransportesRendidos.map(item => [item.id_transporte_provincial.toString(), { ...item, type: 'transporte' }])
            ]);
            const allDetalles = [
                ...detallesCompras.map(item => ({ ...item, type: 'compra' })),
                ...detallesViajes.map(item => ({ ...item, type: 'viatico' })),
                ...detallesTransportes.map(item => ({ ...item, type: 'transporte' }))
            ];

            updatePanelMontosRendicion(montoSolicitado, montoRendido);

            if (allDetalles.length > 0) {
                if (detallesCompras.length > 0) {
                    const comprasSection = document.createElement('div');
                    comprasSection.innerHTML = '<h3>Compras Menores</h3>';
                    detallesContainer.appendChild(comprasSection);
                    allDetalles.filter(item => item.type === 'compra').forEach(item => {
                        const rendido = rendidosMap.get(item.id.toString())?.type === 'compra' ? rendidosMap.get(item.id.toString()) : null;
                        renderItem(item, rendido, detallesContainer, data.id, latestEstado);
                    });
                }
                if (detallesViajes.length > 0) {
                    const viaticosSection = document.createElement('div');
                    viaticosSection.innerHTML = '<h3>Viáticos</h3>';
                    detallesContainer.appendChild(viaticosSection);
                    allDetalles.filter(item => item.type === 'viatico').forEach(item => {
                        const rendido = rendidosMap.get(item.id.toString())?.type === 'viatico' ? rendidosMap.get(item.id.toString()) : null;
                        renderItem(item, rendido, detallesContainer, data.id, latestEstado);
                    });
                }
                if (detallesTransportes.length > 0) {
                    const transportesSection = document.createElement('div');
                    transportesSection.innerHTML = '<h3>Transportes</h3>';
                    detallesContainer.appendChild(transportesSection);
                    allDetalles.filter(item => item.type === 'transporte').forEach(item => {
                        const rendido = rendidosMap.get(item.id.toString())?.type === 'transporte' ? rendidosMap.get(item.id.toString()) : null;
                        renderItem(item, rendido, detallesContainer, data.id, latestEstado);
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
    }); /*Posiblemente se requiera eliminar dicha funcion y restaurarla a su estado anterior para que se realicen las autorizaciones de diferente manera.*/

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