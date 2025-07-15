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
                        fetch(`rendiciones/getDetallesRendidosByRendicion?id_rendicion=${encodeURIComponent(idRendicion)}`).then(res => res.json())
                    ])
                    .then(([detallesCompras, detallesRendidos]) => {
                        const detallesContainer = document.getElementById("detalles-compras-container");
                        detallesContainer.innerHTML = '';
                        const rendidosMap = new Map(detallesRendidos.map(item => [item.id_detalle_compra.toString(), item]));

                        if (detallesCompras.length > 0) {
                            detallesCompras.forEach(item => {
                                const rendido = rendidosMap.get(item.id.toString());
                                const container = document.createElement('div');
                                container.className = 'container-detalle';
                                const isRendido = !!rendido;
                                container.innerHTML = `
                                    <div class="compras-elementos-uno">
                                        <div class="modal-element">
                                            <span class="placeholder">Descripcion</span>
                                            <input type="text" class="rendicion-element" value="${item.descripcion || ''}" readonly>
                                        </div>
                                        <div class="modal-element">
                                            <span class="placeholder">Motivo</span>
                                            <input type="text" class="rendicion-element" value="${item.motivo || ''}" readonly>
                                        </div>
                                        <div class="modal-element">
                                            <span class="placeholder">Monto Solicitado</span>
                                            <input type="text" class="rendicion-element" value="${item.importe || '0.00'}" readonly>
                                        </div>
                                        <div class="modal-element">
                                            <span class="placeholder">Monto Rendido</span>
                                            <input type="number" step="0.01" class="rendicion-element monto-rendido" value="${isRendido ? rendido.monto_rendido : '0.00'}" required>
                                        </div>
                                    </div>
                                    <div class="compras-elementos-dos">
                                        <div class="modal-element">
                                            <span class="placeholder">Fecha</span>
                                            <input type="date" class="rendicion-element fecha-rendicion" value="${isRendido ? rendido.fecha : new Date().toISOString().split('T')[0]}" required>
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
                                detallesContainer.appendChild(container);

                                const fileInput = container.querySelector('.file-input');
                                const adjuntarBtn = container.querySelector('.btn-adjuntar');
                                const archivoNombre = container.querySelector('.archivo-nombre');

                                adjuntarBtn.addEventListener('click', () => fileInput.click());
                                fileInput.addEventListener('change', function(e) {
                                    const file = e.target.files[0];
                                    if (file) {
                                        archivoNombre.textContent = file.name;
                                        archivoNombre.href = URL.createObjectURL(file); // Previsualización temporal
                                    }
                                });

                                const guardarItemBtn = container.querySelector('.btn-guardar-item');
                                guardarItemBtn.addEventListener('click', function(e) {
                                    e.preventDefault(); // Prevenir comportamiento por defecto
                                    const detalle = {
                                        id_detalle_compra: item.id,
                                        descripcion: item.descripcion,
                                        motivo: item.motivo,
                                        montoSolicitado: item.importe,
                                        montoRendido: container.querySelector('.monto-rendido').value,
                                        fecha: container.querySelector('.fecha-rendicion').value,
                                        archivo: fileInput.files[0] || (isRendido && rendido.archivo_adjunto ? null : undefined)
                                    };
                                    guardarItemIndividual(detalle, idRendicion, container);
                                });

                                if (isRendido && rendido.archivo_adjunto) {
                                    archivoNombre.href = 'uploads/' + rendido.archivo_adjunto;
                                }
                            });
                        } else {
                            detallesContainer.innerHTML = '<p>No hay detalles de compras menores válidos.</p>';
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

    function guardarItemIndividual(detalle, id_rendicion, container) {
        const formData = new FormData();
        formData.append('id_rendicion', id_rendicion);
        formData.append('id_detalle_compra', detalle.id_detalle_compra);
        formData.append('descripcion', detalle.descripcion);
        formData.append('motivo', detalle.motivo);
        formData.append('montoSolicitado', detalle.montoSolicitado);
        formData.append('montoRendido', detalle.montoRendido);
        formData.append('fecha', detalle.fecha);
        if (detalle.archivo instanceof File) {
            formData.append('archivo', detalle.archivo);
        } else if (detalle.archivo && typeof detalle.archivo === 'object' && detalle.archivo.name) {
            // Mantener el nombre del archivo existente sin enviarlo como archivo
            formData.append('archivo_existente', detalle.archivo.name);
        }

        fetch('rendiciones/guardarItemRendido', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Ítem guardado o actualizado exitosamente.");
                const archivoNombre = container.querySelector('.archivo-nombre');
                if (detalle.archivo instanceof File) {
                    archivoNombre.textContent = detalle.archivo.name;
                    archivoNombre.href = URL.createObjectURL(detalle.archivo);
                }
            } else {
                alert("Error al guardar el ítem: " + (data.error || 'Intente de nuevo'));
            }
        })
        .catch(error => {
            console.error('Error al guardar ítem: ', error);
            alert("Error al guardar el ítem");
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