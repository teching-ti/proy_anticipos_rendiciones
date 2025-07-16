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
            if (currentStep === 0) {
                const idAnticipo = document.getElementById("id-anticipo").value;
                const idRendicion = document.getElementById("id-rendicion").value;
                if (idAnticipo && idRendicion) {
                    Promise.all([
                        fetch(`rendiciones/getDetallesComprasMenores?id_anticipo=${encodeURIComponent(idAnticipo)}`).then(res => res.json()),
                        fetch(`rendiciones/getDetallesViajes?id_anticipo=${encodeURIComponent(idAnticipo)}`).then(res => res.json()),
                        fetch(`rendiciones/getDetallesTransportes?id_anticipo=${encodeURIComponent(idAnticipo)}`).then(res => res.json()),
                        fetch(`rendiciones/getDetallesRendidosByRendicion?id_rendicion=${encodeURIComponent(idRendicion)}`).then(res => res.json()),
                        fetch(`rendiciones/getDetallesViajesRendidosByRendicion?id_rendicion=${encodeURIComponent(idRendicion)}`).then(res => res.json()),
                        fetch(`rendiciones/getDetallesTransportesRendidosByRendicion?id_rendicion=${encodeURIComponent(idRendicion)}`).then(res => res.json())
                    ])
                    .then(([detallesCompras, detallesViajes, detallesTransportes, detallesComprasRendidos, detallesViajesRendidos, detallesTransportesRendidos]) => {
                        const detallesContainer = document.getElementById("detalles-compras-container");
                        detallesContainer.innerHTML = '';

                        console.log(detallesViajes);
                        console.log(detallesTransportes);

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

                        if (allDetalles.length > 0) {
                            
                            // Sección de compras
                            if(detallesCompras.length > 0){
                                const comprasSection = document.createElement('div');
                                comprasSection.innerHTML = '<h3>Compras Menores</h3>';
                                detallesContainer.appendChild(comprasSection);
                                allDetalles.filter(item => item.type === 'compra').forEach(item => {
                                    const rendido = rendidosMap.get(item.id.toString())?.type === 'compra' ? rendidosMap.get(item.id.toString()) : null;
                                    renderItem(item, rendido, detallesContainer, idRendicion);
                                });
                            }

                            
                            
                            // Sección de viáticos
                            if (detallesViajes.length > 0) {
                                const viaticosSection = document.createElement('div');
                                viaticosSection.innerHTML = '<h3>Viáticos</h3>';
                                detallesContainer.appendChild(viaticosSection);
                                allDetalles.filter(item => item.type === 'viatico').forEach(item => {
                                    const rendido = rendidosMap.get(item.id.toString())?.type === 'viatico' ? rendidosMap.get(item.id.toString()) : null;
                                    renderItem(item, rendido, detallesContainer, idRendicion);
                                });
                            }

                            // Sección de transportes
                            if (detallesTransportes.length > 0) {
                                const transportesSection = document.createElement('div');
                                transportesSection.innerHTML = '<h3>Transportes</h3>';
                                detallesContainer.appendChild(transportesSection);
                                allDetalles.filter(item => item.type === 'transporte').forEach(item => {
                                    const rendido = rendidosMap.get(item.id.toString())?.type === 'transporte' ? rendidosMap.get(item.id.toString()) : null;
                                    renderItem(item, rendido, detallesContainer, idRendicion);
                                });
                            }
                        } else {
                            detallesContainer.innerHTML = '<p>No hay detalles válidos.</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar detalles: ', error);
                        alert("No se pudieron cargar los detalles");
                    });
                }
            }
            showStep(currentStep + 1);
        }
    }
    
    function prevStep() {
        if (currentStep > 0) {
            showStep(currentStep - 1);
        }
    }

    function renderItem(item, rendido, container, idRendicion) {
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
                    <span class="placeholder">${item.type === 'compra' ? 'Descripcion' : item.type === 'transporte' ? 'Tipo de transporte' : 'Concepto'}</span>
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
                    <input type="number" step="0.01" class="rendicion-element monto-rendido" value="${isRendido ? rendido.monto_rendido : '0.00'}" required>
                </div>
            </div>
            <div class="compras-elementos-dos">
                <div class="modal-element">
                    <span class="placeholder">Fecha de Rendición</span>
                    <input type="date" class="rendicion-element fecha-rendicion" value="${isRendido ? rendido.fecha : new Date().toISOString().split('T')[0]}" readonly required>
                </div>
                <div class="modal-element">
                    <input type="file" class="file-input" style="display: none;">
                    <div class="btn btn-adjuntar"><i class="fa-solid fa-file-invoice"></i> Adjuntar</div>
                </div>
                <p class="enlace-factura">
                    <a ${isRendido && rendido.archivo_adjunto ? 'href=#' : ''} class="archivo-nombre" target="_blank">${isRendido && rendido.archivo_adjunto ? rendido.archivo_adjunto : 'Sin archivo'}</a>
                </p>
            </div>
            <div class="modal-footer-item">
                <button class="btn btn-default btn-guardar-item">Guardar ítem</button>
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
                guardarItemIndividual(detalle, idRendicion, itemContainer).then(() => guardarItemBtn.disabled = false);
            } else if (item.type === 'viatico') {
                guardarItemViaje(detalle, idRendicion, itemContainer).then(() => guardarItemBtn.disabled = false);
            } else if (item.type === 'transporte') {
                guardarItemTransporte(detalle, idRendicion, itemContainer).then(() => guardarItemBtn.disabled = false);
            }
        });

        if (isRendido && rendido.archivo_adjunto) {
            archivoNombre.href = 'uploads/' + rendido.archivo_adjunto;
        }
    }

    function guardarItemIndividual(detalle, id_rendicion, container) {
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
        return fetch('rendiciones/guardarItemRendido', { // Asegurar que devuelva la promesa
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Ítem de compra guardado o actualizado exitosamente.");
                const archivoNombre = container.querySelector('.archivo-nombre');
                if (detalle.archivo instanceof File) {
                    archivoNombre.textContent = detalle.archivo.name;
                    archivoNombre.href = URL.createObjectURL(detalle.archivo);
                }
            } else {
                alert("Error al guardar el ítem de compra: " + (data.error || 'Intente de nuevo'));
            }
        })
        .catch(error => {
            console.error('Error al guardar ítem de compra: ', error);
            alert("Error al guardar el ítem de compra");
        });
    }

    function guardarItemViaje(detalle, id_rendicion, container) {
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
        return fetch('rendiciones/guardarItemViaje', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Ítem de viático guardado o actualizado exitosamente.");
                const archivoNombre = container.querySelector('.archivo-nombre');
                if (detalle.archivo instanceof File) {
                    archivoNombre.textContent = detalle.archivo.name;
                    archivoNombre.href = URL.createObjectURL(detalle.archivo);
                }
            } else {
                alert("Error al guardar el ítem de viático: " + (data.error || 'Intente de nuevo'));
            }
        })
        .catch(error => {
            console.error('Error al guardar ítem de viático: ', error);
            alert("Error al guardar el ítem de viático");
        });
    }

    function guardarItemTransporte(detalle, id_rendicion, container) {
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
        return fetch('rendiciones/guardarItemTransporte', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Ítem de transporte guardado o actualizado exitosamente.");
                const archivoNombre = container.querySelector('.archivo-nombre');
                if (detalle.archivo instanceof File) {
                    archivoNombre.textContent = detalle.archivo.name;
                    archivoNombre.href = URL.createObjectURL(detalle.archivo);
                }
            } else {
                alert("Error al guardar el ítem de transporte: " + (data.error || 'Intente de nuevo'));
            }
        })
        .catch(error => {
            console.error('Error al guardar ítem de transporte: ', error);
            alert("Error al guardar el ítem de transporte");
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
    const detallesContainer = document.createElement('div');
    detallesContainer.id = 'detalles-compras-container';
    detallesContainer.style.display = 'none'; // Oculto inicialmente
    completarRendicionModal.querySelector('.modal-body').appendChild(detallesContainer);


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
        departamentoResponsable.setAttribute("data-departamento",`${data.departamento}` );
        departamentoResponsable.value = `${data.departamento_nombre}`;
        cargoResponsable.value = `${data.cargo}`;
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
    });

})