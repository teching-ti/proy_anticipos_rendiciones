document.addEventListener('DOMContentLoaded', async () => {

    const btnRefresh = document.getElementById("btn-refresh");
    btnRefresh.addEventListener("click", ()=>{
        window.location.reload();
    })

    // Funcionalidad que se utiliza para mostrar el formulario que permite crear un anticipo
    function openAddAnticipoModal() {
        const modal = document.getElementById('addAnticipoModal');
        if (modal) {
            modal.style.display = 'block';
        }
    }

    // Funcionalidad que se ejecuta tras cargar la página de anticipos, verifica si se encuentra algún parámetro en la url
    // si eexiste un parámetros dentro llamado openModal, entonces usará la funcionalidad openAddAnticipoModal
    window.addEventListener('load', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('openModal') === 'true') {
            openAddAnticipoModal();
            // Limpia el parámetro de la url para no mostrarlo, es opcional
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    // Lógica para activar o bloquear el boton de creación de anticipo
    const userAnticipo = await fetch(`anticipos/getAnticipoPendiente`);
    const userEstadoAnticipos = await userAnticipo.json();
    if(userEstadoAnticipos){
        document.querySelector(".btn-add-anticipo").classList.add("bloq-anticipo-pendiente");
        document.querySelector('.btn-add-anticipo').addEventListener('click', () => alert('Estimado usuario. Usted aún tiene un anticipo en proceso, no podrá generar otra solicitud hasta que su anticipo actual se encuentre en estado "Rendido".'));
    }else{
        document.querySelector('.btn-add-anticipo').addEventListener('click', () => openModal('addAnticipoModal'));
    }

    const fechaHoy = new Date();
    const fechaFormateada = fechaHoy.toISOString().split('T')[0];
    let inputFecha = document.getElementById("fecha_solicitud");
    inputFecha.value = fechaFormateada;

    // Funciones para abrir y cerrar modales
    function openModal(modalId) {
        document.getElementById(modalId).style.display="block";
        //document.getElementById(modalId).classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display="none";
    }

    // Validación de campos (solo letras, números y espacios)
    function validateInput(input) {
        const regex = /^[a-zA-Z0-9\sáéíóúÁÉÍÓÚñÑ&]+$/;
        return regex.test(input);
    }
    
    // Validar formularios de aprobar/rechazar
    const actionForms = document.querySelectorAll('form[action*="/anticipos/approve"], form[action*="/anticipos/reject"]');
    actionForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!validateActionForm(form)) {
                e.preventDefault();
            }
        });
    });

    //document.querySelector('.btn-add-anticipo').addEventListener('click', () => openModal('addAnticipoModal'));

    // Botones "Cerrar" modal
    document.querySelectorAll('.btn-close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modal;
            closeModal(modalId);
        });
    });

    // Agregar Nombre del proyecto
    document.getElementById("codigo_sscc").addEventListener("change", function(){
        //console.log("Cambiando");
        const selectedOption = this.options[this.selectedIndex];
        const text = selectedOption.text.split(':');// antes aquí había un -
        const txtNombreProyecto = text[1].trim();
        document.getElementById("nombre_proyecto").value=txtNombreProyecto;
    });

    // Funcionalidad para cambiar pestañas dentro del formulario de creación de anticipos
    document.querySelectorAll(".form-step").forEach(step => steps.push(step));
    showStep(currentStep);

    // Selección y cambio de vista en sección de Concepto - compras menores o viajes
    const opcionesConcepto = document.querySelectorAll("input[name='concepto']");
    const panelComprasMenores = document.getElementById("panel-compras-menores");
    const panelViajes = document.getElementById("panel-viajes");

    // Funcionalidad encargada de mostrar y ocultar el panel de Compras o Viajes
    function cambioConcepto(){
        if (document.getElementById("compras-menores").checked) {
            panelComprasMenores.style.display = "block";
            panelViajes.style.display = "none";
        } else if (document.getElementById("viajes").checked) {
            panelComprasMenores.style.display = "none";
            panelViajes.style.display = "block";
        }
    }

    opcionesConcepto.forEach(radioBtn => {
        radioBtn.addEventListener("change", cambioConcepto);
    })

    cambioConcepto();
    // selección de concepto

    // sección de tabs para personas viaje
    // Evento para cambiar entre pestañas
    document.getElementById("tabs-header").addEventListener("click", function(e) {
        if (e.target.classList.contains("tab-button") && !e.target.classList.contains("add-tab")) {
            const tabId = e.target.getAttribute("data-tab");
            activarTab(tabId);
        }
    });

    // Evento para agregar nueva pestaña
    document.getElementById("add-tab").addEventListener("click", agregarNuevaPersona);

    // función de búsqueda
    document.getElementById("input-buscar-anticipo").addEventListener("input", function() {
        const filter = this.value.toLowerCase();
        // console.log(filter);

        const rows = document.querySelectorAll("#table-body tr");

        rows.forEach(row => {
            // convierte el texto de la fila en un solo string para buscar coincidencias en cualquier columna
            const rowText = row.textContent.toLowerCase();
            // muestra u oculta la fila según si coincide o no con el filtro
            row.style.display = rowText.includes(filter) ? "" : "none";
        });
    });
    
});

// Funcionalidad para mostrar ícono de carga de la página tras ingresar/ actualizar
window.addEventListener("load", function(){
    let anticiposContent = document.querySelector(".anticipos-content");
    anticiposContent.style.display = "block";
    let loadingModalSection = document.getElementById('loadingModalPage');
    loadingModalSection.style.display = "none";
})

/*Inicia funcionalidad para cambiar de pestañas dentro del formulario de creación de anticipos*/
// steps del formulario
let currentStep = 0;
const steps = [];

function showStep(index) {
    steps.forEach((step, i) => {
        step.classList.toggle("active", i === index);
    });
    currentStep = index;
}

function nextStep() {
    
    const currentStepElement = steps[currentStep];
    const requiredInputs = currentStepElement.querySelectorAll('[required]');

    let allFieldsValid = true;
    requiredInputs.forEach(input => {
        // La validación nativa del navegador para 'required'
        if (!input.checkValidity()) {
            allFieldsValid = false;
            // Opcional: enfocar el campo inválido para que el usuario lo vea
            input.focus(); 
            // Detener el bucle una vez que se encuentre un campo inválido
            return; 
        }
    });

    if (allFieldsValid) {
        if (currentStep < steps.length - 1) {
            showStep(currentStep + 1);
        }
    } else {
        // Opcional: mostrar un mensaje de error si la validación falla
        alert('Por favor, completa todos los campos obligatorios.');
    }

    //console.log(requiredInputs);
    // if (currentStep < steps.length - 1) {
    //     showStep(currentStep + 1);
    // }
}

function prevStep() {
    if (currentStep > 0) {
        showStep(currentStep - 1);
    }
}

// exportación global
window.nextStep = nextStep;
window.prevStep = prevStep;
/*Termina funcionalidad para cambiar de pestañas dentro del formulario de creación de anticipos*/

function terminar(){
    console.log("Completado");
}

/*Inicia funcionalidad para tabs de personas viajes*/
let personaCount = 0;
let personaIndices = [0];

function activarTab(tabId) {
    // Desactivar pestañas
    document.querySelectorAll(".tab-button").forEach(btn => btn.classList.remove("active"));
    document.querySelectorAll(".tab-content").forEach(content => content.classList.remove("active"));

    document.querySelector(`.tab-button[data-tab="${tabId}"]`)?.classList.add("active");
    document.getElementById(tabId)?.classList.add("active");
}

// Esta funcionalidad se da cada vez que presionamos el botón para añadir más personas
async function agregarNuevaPersona() {
    // Buscar el menor índice libre
    let newIndex = 1;

    while (personaIndices.includes(newIndex)) {
        newIndex++;
    }

    // console.log("Nueva Persona", newIndex);
    personaIndices.push(newIndex);
    personaIndices.sort((a, b) => a - b);

    // Crear botón de pestaña
    const newTabBtn = document.createElement("div");
    newTabBtn.className = "tab-button";
    newTabBtn.dataset.tab = `persona-${newIndex}`;
    newTabBtn.textContent = `Persona ${newIndex}`;
    newTabBtn.id = `tab-persona-${newIndex}`;
    document.getElementById("tabs-header").insertBefore(newTabBtn, document.getElementById("add-tab"));

    // Obtener valores de Persona 1 para copiar
    const persona1DiasAlimentacion = document.querySelector('[name="dias-alimentacion-1"]')?.value || '';
    const persona1DiasMovilidad = document.querySelector('[name="dias-movilidad-1"]')?.value || '';
    const persona1DiasHospedaje = document.querySelector('[name="dias-hospedaje-1"]')?.value || '';

    // Obtener ítems de Transporte Provincial de Persona 1
    // Aquí se modificó la funcionalidad para que se permite editar el número de días de viático para los casos de movilidad, hospedaje y alimentación, se retira el readonly pero no las condiciones
    const persona1TranspProvList = document.getElementById('transp-prov-list-1');
    let transpProvContent = '';
    if (persona1TranspProvList) {
        const items = persona1TranspProvList.querySelectorAll('.transp-prov-element');
        items.forEach((item, itemIndex) => {
            const oldIndex = 1; // Índice de Persona 1
            const newItemIndex = itemIndex + 1; // mantener el orden de los ítems

            // Obtener valores de Persona 1
            const tipoTransporte = item.querySelector('input[name="tipo-transporte-1-' + newItemIndex + '"]:checked')?.value || 'terrestre';
            const ciudadOrigen = item.querySelector('[name="ciudad-origen-1-' + newItemIndex + '"]')?.value || '';
            const ciudadDestino = item.querySelector('[name="ciudad-destino-1-' + newItemIndex + '"]')?.value || '';
            const fecha = item.querySelector('[name="fecha-1-' + newItemIndex + '"]')?.value || '';
            const monto = item.querySelector('[name="gasto-viaje-1-' + newItemIndex + '"]')?.value || '';

            const grupo = document.createElement("div");
            grupo.classList.add("transp-prov-element");
            grupo.innerHTML = `
                <div class="med-transporte">
                    <div ${newIndex === 1 ? '' : 'style="display: none;"'}>
                        <input type="radio" name="tipo-transporte-${newIndex}-${newItemIndex}" id="terrestre-${newIndex}-${newItemIndex}" value="terrestre" ${tipoTransporte === 'terrestre' ? 'checked' : ''}>
                        <label for="terrestre-${newIndex}-${newItemIndex}">Terrestre</label>
                    </div>
                    <div ${newIndex === 1 ? '' : 'style="display: none;"'}>
                        <input type="radio" name="tipo-transporte-${newIndex}-${newItemIndex}" id="aereo-${newIndex}-${newItemIndex}" value="aereo" ${tipoTransporte === 'aereo' ? 'checked' : ''}>
                        <label for="aereo-${newIndex}-${newItemIndex}">Aéreo</label>
                    </div>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Ciudad Origen</span>
                    <input type="text" class="form-control" name="ciudad-origen-${newIndex}-${newItemIndex}" value="${ciudadOrigen}" ${newIndex === 1 ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Ciudad Destino</span>
                    <input type="text" class="form-control" name="ciudad-destino-${newIndex}-${newItemIndex}" value="${ciudadDestino}" ${newIndex === 1 ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Fecha</span>
                    <input type="date" class="form-control" name="fecha-${newIndex}-${newItemIndex}" value="${fecha}" required ${newIndex === 1 ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Monto</span>
                    <input type="number" class="form-control gasto-viaje" name="gasto-viaje-${newIndex}-${newItemIndex}" value="${monto}" required ${newIndex === 1 ? '' : 'readonly'}>
                </div>
            `;
            transpProvContent += grupo.outerHTML;
        });
    }

    // ajax para obtener cargos
    const cargos = await obtenerCargosDesdeBD();
    let cargoOptions = `<option value="">- Seleccionar cargo -</option>`;
    cargos.forEach(c => {
        cargoOptions += `<option value="${c.id}">${c.nombre}</option>`;
    });

    // Crear contenido
    const tabBody = document.getElementById("tabs-body");
    const newTabContent = document.createElement("div");
    newTabContent.className = "tab-content";
    newTabContent.id = `persona-${newIndex}`;

    newTabContent.innerHTML = `
        <div class="modal-element element-doc-id">
            <span class="placeholder">Doc. Id ${newIndex}</span>
            <input type="text" class="form-control" name="doc-id-${newIndex}" data-index="${newIndex}" required>
            <span class="lupa" data-index="${newIndex}">
                <i class="fa-solid fa-xl fa-magnifying-glass"></i>
            </span>
        </div>
        <div class="modal-element">
            <span class="placeholder">Nombre de Persona ${newIndex}</span>
            <input type="text" class="form-control" name="persona-nombre-${newIndex}" data-index="${newIndex}" required>
        </div>
        <div class="modal-element">
            <span class="placeholder">Cargo persona ${newIndex}</span>
            <select class="form-control select-cargo" name="cargo-nombre-${newIndex}" data-index="${newIndex}" required>
                ${cargoOptions}
            </select>
        </div>

        <h4 class="viaje-sub-title">Transporte Provincial
            <div class="icon-container toggle-icon" data-target=".section-transporte-provincial-${newIndex}">
                <div class="line horizontal"></div>
                <div class="line vertical"></div>
            </div>
        </h4>
        <hr class="separador-viaje-subtitle">
        <div class="viaje-element section-transporte-provincial-${newIndex}" style="display: none;">
            <div class="transporte-prov-list" id="transp-prov-list-${newIndex}">${transpProvContent}</div>
            <div class="btn add-transp-provincial" data-persona="${newIndex}" ${newIndex == 1 ? '' : 'style="display: none;"'}>Añadir</div>
        </div>

        <h4 class="viaje-sub-title">Hospedaje
            <div class="icon-container toggle-icon" data-target=".section-hospedaje-${newIndex}">
                <div class="line horizontal"></div>
                <div class="line vertical"></div>
            </div>
        </h4>
        <hr class="separador-viaje-subtitle">
        <div class="viaje-element section-hospedaje-${newIndex}" style="display: none;">
            <div class="modal-element">
                <span class="placeholder">Días</span>
                <input type="number" class="form-control" name="dias-hospedaje-${newIndex}" value="${persona1DiasHospedaje}" ${newIndex == 1 ? '' : ''} >
            </div>
            <div class="modal-element">
                <span class="placeholder">Monto</span>
                <input type="number" class="form-control monto-hospedaje" name="monto-hospedaje-${newIndex}" readonly>
            </div>
        </div>

        <h4 class="viaje-sub-title">Movilidad
            <div class="icon-container toggle-icon" data-target=".section-movilidad-${newIndex}">
                <div class="line horizontal"></div>
                <div class="line vertical"></div>
            </div>
        </h4>
        <hr class="separador-viaje-subtitle">
        <div class="viaje-element section-movilidad-${newIndex}" style="display: none;">
            <div class="modal-element">
                <span class="placeholder">Días</span>
                <input type="number" class="form-control" name="dias-movilidad-${newIndex}" value="${persona1DiasMovilidad}" ${newIndex == 1 ? '' : ''}>
            </div>
            <div class="modal-element">
                <span class="placeholder">Monto</span>
                <input type="number" class="form-control monto-movilidad" name="monto-movilidad-${newIndex}" readonly>
            </div>
        </div>

        <h4 class="viaje-sub-title">Alimentación
            <div class="icon-container toggle-icon" data-target=".section-alimentacion-${newIndex}">
                <div class="line horizontal"></div>
                <div class="line vertical"></div>
            </div>
        </h4>
        <hr class="separador-viaje-subtitle">
        <div class="viaje-element section-alimentacion-${newIndex}" style="display: none;">
            <div class="modal-element">
                <span class="placeholder">Días</span>
                <input type="number" class="form-control" name="dias-alimentacion-${newIndex}" value="${persona1DiasAlimentacion}" ${newIndex == 1 ? '' : ''}>
            </div>
            <div class="modal-element">
                <span class="placeholder">Monto</span>
                <input type="number" class="form-control monto-alimentacion" name="monto-alimentacion-${newIndex}" readonly>
            </div>
        </div>
        <div class='container-remove-persona'>
            <div class="btn remove-persona-btn" data-index="${newIndex}" ${newIndex === 1 ? 'style="display: none;"' : ''}>Eliminar</div>
        </div>
    `;

    tabBody.appendChild(newTabContent);
    activarTab(`persona-${newIndex}`);
    actualizarBotonesEliminar();

    // Función para calcular el monto de un concepto
    function calcularMonto(index, tipo) {
        const diasInput = document.querySelector(`[name='dias-${tipo}-${index}']`);
        const montoInput = document.querySelector(`[name='monto-${tipo}-${index}']`);
        const container = document.getElementById(`persona-${index}`);
        const tarifas = JSON.parse(container.dataset.tarifas || '{}');
        const montoPorDia = tarifas[tipo] || 0;
        const dias = parseInt(diasInput.value) || 0;
        if (!isNaN(dias) && dias >= 0) {
            montoInput.value = (dias * montoPorDia).toFixed(2);
        } else {
            montoInput.value = '';
        }
        actualizarTotalGastos();
    }

    // Escuchar los cambios en el select para colocar los montos
    newTabContent.querySelector(".select-cargo").addEventListener("change", async function () {
        const cargoId = this.value;
        const index = this.dataset.index;

        if (!cargoId) {
            // Si no se selecciona cargo, deshabilitar inputs de días y limpiar montos
            ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
                const diasInput = document.querySelector(`[name='dias-${tipo}-${index}']`);
                const montoInput = document.querySelector(`[name='monto-${tipo}-${index}']`);
                diasInput.setAttribute('readonly', '');
                diasInput.value = '';
                montoInput.value = '';
            });
            const container = document.getElementById(`persona-${index}`);
            container.dataset.tarifas = '{}';
            actualizarTotalGastos();
            return;
        }

        // Obtener tarifas del cargo
        const res = await fetch(`tarifario/montosCargo?cargo_id=${cargoId}`);
        const data = await res.json();

        const tarifas = {};
        data.forEach(d => {
            tarifas[d.concepto.toLowerCase()] = parseFloat(d.monto);
        });

        // Guardar en dataset del contenedor
        const container = document.getElementById(`persona-${index}`);
        container.dataset.tarifas = JSON.stringify(tarifas);

        // Recalcular montos para los días ya ingresados
        ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
            calcularMonto(index, tipo);
        });
    });

    // Conectar cálculo automático para los inputs de días
    function conectarCalculoAutomatico(index, tipo) {
        const diasInput = document.querySelector(`[name='dias-${tipo}-${index}']`);
        diasInput.addEventListener("input", () => {

            /*Inicia lógica para colocar un número máximo de días - agregar anticipo*/
            // Obtener el límite de días
            const fechaInicio = document.getElementById("fecha_ejecucion").value;
            const fechaFin = document.getElementById("fecha_finalizacion").value;

            if(fechaInicio && fechaFin){
                const inicio = new Date(fechaInicio);
                const fin = new Date(fechaFin);

                if(fin >= inicio){
                    const diffDias = Math.floor((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;
                    const valor = parseInt(diasInput.value) || 0;
                    
                    // diffDias, es el número de días permitido

                    if (valor > diffDias) {
                        alert(`El número máximo de días permitido es ${diffDias}.`);
                        diasInput.value = 0;
                    }
                }
            }
            /*Termina lógica para colocar un número máximo de días - agregar anticipo*/
            calcularMonto(index, tipo);

            if (index == 1) {
                actualizarOtrasPersonas(tipo);
            }
        });
    }

    // Función para actualizar los campos de las demás personas
    function actualizarOtrasPersonas(tipo) {
        const valorPersona1 = document.querySelector(`[name='dias-${tipo}-1']`)?.value || '';
        personaIndices.forEach(index => {
            if (index !== 1) {
                const diasInput = document.querySelector(`[name='dias-${tipo}-${index}']`);
                if (diasInput) {
                    diasInput.value = valorPersona1;
                    calcularMonto(index, tipo);
                }
            }
        });
    }

    // Implementación de funcionalidad para que se realice el recálculo correspondiente
    ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
        conectarCalculoAutomatico(newIndex, tipo);
    });

    // Escuchar cambios en los inputs de transporte provincial de Persona 1
    function conectarActualizacionTransporte() {
        const persona1TranspList = document.getElementById('transp-prov-list-1');
        if (persona1TranspList) {
            persona1TranspList.querySelectorAll('.transp-prov-element').forEach((item, itemIndex) => {
                const newItemIndex = itemIndex + 1;
                const inputs = {
                    ciudadOrigen: item.querySelector(`[name='ciudad-origen-1-${newItemIndex}']`),
                    ciudadDestino: item.querySelector(`[name='ciudad-destino-1-${newItemIndex}']`),
                    fecha: item.querySelector(`[name='fecha-1-${newItemIndex}']`),
                    monto: item.querySelector(`[name='gasto-viaje-1-${newItemIndex}']`)
                };

                Object.values(inputs).forEach(input => {
                    if (input) {
                        input.addEventListener("input", () => {
                            const values = {
                                ciudadOrigen: inputs.ciudadOrigen?.value || '',
                                ciudadDestino: inputs.ciudadDestino?.value || '',
                                fecha: inputs.fecha?.value || '',
                                monto: inputs.monto?.value || ''
                            };
                            personaIndices.forEach(index => {
                                if (index !== 1) {
                                    const otherItem = document.querySelector(`#transp-prov-list-${index} .transp-prov-element:nth-child(${itemIndex + 1})`);
                                    if (otherItem) {
                                        otherItem.querySelector(`[name='ciudad-origen-${index}-${newItemIndex}']`).value = values.ciudadOrigen;
                                        otherItem.querySelector(`[name='ciudad-destino-${index}-${newItemIndex}']`).value = values.ciudadDestino;
                                        otherItem.querySelector(`[name='fecha-${index}-${newItemIndex}']`).value = values.fecha;
                                        otherItem.querySelector(`[name='gasto-viaje-${index}-${newItemIndex}']`).value = values.monto;
                                    }
                                }
                            });
                            actualizarTotalGastos();
                        });
                    }
                });
            });
        }
    }

    // Escuchar cambios en el nuevo input de monto
    newTabContent.querySelector(".monto-hospedaje").addEventListener("input", actualizarTotalGastos);
    newTabContent.querySelector(".monto-movilidad").addEventListener("input", actualizarTotalGastos);
    newTabContent.querySelector(".monto-alimentacion").addEventListener("input", actualizarTotalGastos);

    // Escuchar los clicks en lupa para autocompletar nombres del trabajador
    newTabContent.querySelector(".lupa").addEventListener("click", async function(){
        const index = this.dataset.index;
        const docId = document.querySelector(`[name='doc-id-${index}']`).value;

        if (!docId.trim() || docId.trim().length<8) {
            alert("El número ingresado no es válido"); return; 
        }

        const inputNombres = document.querySelector(`[name=persona-nombre-${index}]`);
        const res = await fetch(`usuarios/anticipoBuscarDni?doc-identidad=${docId}`);
        const data = await res.json();
        
        //console.log(data);
        
        if(data.success){
            inputNombres.value = `${data.data.nombres} ${data.data.apellidos}`;
        }else{
            alert("No se encontraron datos del trabajador.");
        }
    });

    // Conectar actualización de transporte para Persona 1
    if (newIndex === 1) {
        conectarActualizacionTransporte();
    }
}

function actualizarBotonesEliminar() {
    // Oculta todos los botones de eliminar
    document.querySelectorAll(".remove-persona-btn").forEach(btn => btn.style.display = "none");

    const max = Math.max(...personaIndices);
    const btn = document.querySelector(`#persona-${max} .remove-persona-btn`);
    if (btn) btn.style.display = "inline-block";
}

document.getElementById("tabs-body").addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-persona-btn")) {

        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea eliminar este item, esta acción no se puede deshacer?',
            type: 'confirm',
            event: 'confirm'
        });

        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');

        acceptButton.onclick = () => {
          
            const index = parseInt(e.target.dataset.index, 10);

            // Eliminar tab y contenido
            const tab = document.getElementById(`tab-persona-${index}`);
            const content = document.getElementById(`persona-${index}`);
            if (tab) tab.remove();
            if (content) content.remove();

            // Eliminar del array
            personaIndices = personaIndices.filter(i => i !== index);

            // Activar primera pestaña visible
            const firstTab = document.querySelector(".tab-button[data-tab]");
            if (firstTab) activarTab(firstTab.dataset.tab);

            actualizarBotonesEliminar();
            
            // caso actualizar total gastos
            actualizarTotalGastos();

            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };

        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };        
    }
});

// Delegación para añadir transporte provincial dinámicamente por persona
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("add-transp-provincial")) {
        const persona = e.target.dataset.persona;
        const container = document.getElementById(`transp-prov-list-${persona}`);
        const index = container.children.length + 1;

        const grupo = document.createElement("div");
        grupo.classList.add("transp-prov-element");
        grupo.innerHTML = `
            <div class="med-transporte">
                <div>
                    <input type="radio" name="tipo-transporte-${persona}-${index}" id="terrestre-${persona}-${index}" value="terrestre" checked>
                    <label for="terrestre-${persona}-${index}">Terrestre</label>
                </div>
                <div>
                    <input type="radio" name="tipo-transporte-${persona}-${index}" id="aereo-${persona}-${index}" value="aereo">
                    <label for="aereo-${persona}-${index}">Aéreo</label>
                </div>
            </div>
            <div class="modal-element">
                <span class="placeholder">Ciudad Origen</span>
                <input type="text" class="form-control" name="ciudad-origen-${persona}-${index}">
            </div>
            <div class="modal-element">
                <span class="placeholder">Ciudad Destino</span>
                <input type="text" class="form-control" name="ciudad-destino-${persona}-${index}">
            </div>
            <div class="modal-element">
                <span class="placeholder">Fecha</span>
                <input type="date" class="form-control" name="fecha-${persona}-${index}" required>
            </div>
            <div class="modal-element">
                <span class="placeholder">Monto</span>
                <input type="number" class="form-control gasto-viaje" name="gasto-viaje-${persona}-${index}" required>
            </div>
        `;
        container.appendChild(grupo);

        // Sync with other persons when adding from Person 1
        if (persona == 1) {
            personaIndices.forEach(idx => {
                if (idx !== 1) {
                    const otherContainer = document.getElementById(`transp-prov-list-${idx}`);
                    if (otherContainer) {
                        const otherGrupo = document.createElement("div");
                        otherGrupo.classList.add("transp-prov-element");
                        otherGrupo.innerHTML = `
                            <div class="med-transporte">
                                <div>
                                    <input type="radio" name="tipo-transporte-${idx}-${index}" id="terrestre-${idx}-${index}" value="terrestre" checked readonly>
                                    <label for="terrestre-${idx}-${index}">Terrestre</label>
                                </div>
                                <div>
                                    <input type="radio" name="tipo-transporte-${idx}-${index}" id="aereo-${idx}-${index}" value="aereo" readonly>
                                    <label for="aereo-${idx}-${index}">Aéreo</label>
                                </div>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Ciudad Origen</span>
                                <input type="text" class="form-control" name="ciudad-origen-${idx}-${index}" readonly>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Ciudad Destino</span>
                                <input type="text" class="form-control" name="ciudad-destino-${idx}-${index}" readonly>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Fecha</span>
                                <input type="date" class="form-control" name="fecha-${idx}-${index}" required readonly>
                            </div>
                            <div class="modal-element">
                                <span class="placeholder">Monto</span>
                                <input type="number" class="form-control gasto-viaje" name="gasto-viaje-${idx}-${index}" required readonly>
                            </div>
                        `;
                        otherContainer.appendChild(otherGrupo);
                    }
                }
            });
        }

        // Escucha cambios en los nuevos inputs
        if (persona == 1) {
            const newInputs = {
                ciudadOrigen: grupo.querySelector(`[name='ciudad-origen-${persona}-${index}']`),
                ciudadDestino: grupo.querySelector(`[name='ciudad-destino-${persona}-${index}']`),
                fecha: grupo.querySelector(`[name='fecha-${persona}-${index}']`),
                monto: grupo.querySelector(`[name='gasto-viaje-${persona}-${index}']`)
            };
            Object.values(newInputs).forEach(input => {
                if (input) {
                    input.addEventListener("input", () => {
                        const values = {
                            ciudadOrigen: newInputs.ciudadOrigen?.value || '',
                            ciudadDestino: newInputs.ciudadDestino?.value || '',
                            fecha: newInputs.fecha?.value || '',
                            monto: newInputs.monto?.value || ''
                        };
                        personaIndices.forEach(idx => {
                            if (idx !== 1) {
                                const otherItem = document.querySelector(`#transp-prov-list-${idx} .transp-prov-element:nth-child(${index})`);
                                if (otherItem) {
                                    otherItem.querySelector(`[name='ciudad-origen-${idx}-${index}']`).value = values.ciudadOrigen;
                                    otherItem.querySelector(`[name='ciudad-destino-${idx}-${index}']`).value = values.ciudadDestino;
                                    otherItem.querySelector(`[name='fecha-${idx}-${index}']`).value = values.fecha;
                                    otherItem.querySelector(`[name='gasto-viaje-${idx}-${index}']`).value = values.monto;
                                }
                            }
                        });
                        actualizarTotalGastos();
                    });
                }
            });
        }

        // escuchar cambios en el nuevo input de monto
        grupo.querySelector(".gasto-viaje").addEventListener("input", actualizarTotalGastos);
    }
});

document.addEventListener("click", function (e) {
    const icon = e.target.closest(".toggle-icon");
    if (!icon) return;

    const targetSelector = icon.dataset.target;
    const container = icon.closest(".tab-content"); // solo dentro de su propia pestaña
    if (!container) return;

    const targetSection = container.querySelector(targetSelector);
    const isVisible = targetSection && targetSection.style.display === "block";

    // Ocultar solo secciones dentro de esta pestaña
    container.querySelectorAll(".viaje-element").forEach(section => {
        section.style.display = "none";
    });

    // Quitar clase "minus" solo en esta pestaña
    container.querySelectorAll(".toggle-icon").forEach(ic => ic.classList.remove("minus"));

    // Si no estaba visible, mostrarla
    if (!isVisible && targetSection) {
        targetSection.style.display = "block";
        icon.classList.add("minus");
    }
});

/*Agregar gasto menor a 400*/
let gastoCount = 0;

document.getElementById("add-gasto-btn").addEventListener("click", () => {
    gastoCount++;

    const container = document.getElementById("panel-compras-menores");

    const nuevoGasto = document.createElement("div");
    nuevoGasto.classList.add("compras-menores-container");
    nuevoGasto.innerHTML = `
        <div class="remove-gasto-btn"><i class="fa-solid fa-trash-can"></i></div>
        <div class="modal-element">
            <span class="placeholder">Descripción</span>
            <select name="detalles_gastos[${gastoCount}][descripcion]" class="descripcion-gasto" required>
                <option value="">- seleccionar -</option>
                <option value="Combustible">Combustible</option>
                <option value="Peaje">Peaje</option>
                <option value="Parqueo">Parqueo</option>
                <option value="Otros gastos menores">Otros gastos menores</option>
            </select>
        </div>
        <div class="modal-element">
            <span class="placeholder">Motivo</span>
            <input type="text" class="form-control" name="detalles_gastos[${gastoCount}][motivo]" required>
        </div>
        <div class="modal-element">
            <span class="placeholder">Moneda</span>
            <select name="detalles_gastos[${gastoCount}][moneda]" class="moneda form-control" required readonly>
                <option value="pen" selected>S/. PEN</option>
            </select>
        </div>
        <div class="modal-element">
            <span class="placeholder">Monto</span>
            <input type="number" class="form-control monto-gasto" name="detalles_gastos[${gastoCount}][importe]" step="0.01" max="400" required>
        </div>

    `;
    const btnAniadirGasto = document.getElementById("add-gasto-btn");
    container.insertBefore(nuevoGasto, btnAniadirGasto);

    // Escuchar cambios en el select de descripción
    const selectDescripcion = nuevoGasto.querySelector(".descripcion-gasto");
    const inputMonto = nuevoGasto.querySelector(".monto-gasto");
    selectDescripcion.addEventListener("change", () => {
        if (selectDescripcion.value === "Combustible") {
            inputMonto.removeAttribute("max"); // Quitar restricción de 400 para Combustible
            inputMonto.style.outline = "";
        } else {
            inputMonto.setAttribute("max", "400"); // Aplicar restricción para otros

            // Validar el monto actual si ya tiene un valor
            const monto = parseFloat(inputMonto.value);
            if (monto > 400) {
                //alert("El monto no puede ser superior a 400 soles para este tipo de gasto.");
                showAlert({
                    title: 'Advertencia',
                    message: `El monto no puede ser superior a 400 soles para este tipo de gasto.`,
                    type: 'warning',
                    event: 'warning'
                });
                inputMonto.value = "";
                inputMonto.style.outline = "2px solid red";
                inputMonto.focus();
            } else {
                inputMonto.style.outline = ""; // Restaurar estilo si es válido
            }
        }
        actualizarTotalGastos();
    });

    // Escuchar cambios en el input de monto
    inputMonto.addEventListener("input", () => {
        if (selectDescripcion.value !== "Combustible") {
            const monto = parseFloat(inputMonto.value);
            if (monto > 400) {
                // alert("El monto no puede ser superior a 400 soles para este tipo de gasto.");
                showAlert({
                    title: 'Advertencia',
                    message: `El monto no puede ser superior a 400 soles para este tipo de gasto.`,
                    type: 'warning',
                    event: 'warning'
                });
                inputMonto.value = "";
                inputMonto.style.outline = "2px solid red";
                inputMonto.focus();
            } else {
                inputMonto.style.outline = ""; // Restaurar estilo si es válido
            }
        } else {
            inputMonto.style.outline = ""; // Sin restricción para Combustible
        }
        actualizarTotalGastos();
    });

    // Escuchar cambios en el nuevo input de monto
    //nuevoGasto.querySelector(".monto-gasto").addEventListener("input", actualizarTotalGastos);

    // Evento para eliminar este gasto, compras menores
    nuevoGasto.querySelector(".remove-gasto-btn").addEventListener("click", () => {
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea eliminar este item, esta acción no se puede deshacer?',
            type: 'confirm',
            event: 'confirm'
        });

        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');

        acceptButton.onclick = () => {
            nuevoGasto.remove();
            actualizarTotalGastos();
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };

        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    });

    // Llamar para recalcular total
    actualizarTotalGastos();
});

//Calcular el monto total de un anticipo
const montoTotal = document.getElementById("monto-total");

async function actualizarTotalGastos() {
    let total = 0;

    // 1. Sumar montos de gastos menores a 400
    //const montosGastos = document.querySelectorAll("input[name^='gasto-menor-monto-']");
    const montosGastos = document.querySelectorAll("input[name*='detalles_gastos'][name$='[importe]']");
    montosGastos.forEach(input => {
        const valor = parseFloat(input.value);
        if (!isNaN(valor)) total += valor;
    });

    //2. Sumar montos del panel de personas
    const montosViajes = document.querySelectorAll(`
        input[name^='gasto-viaje-'],
        input[name^='monto-hospedaje-'],
        input[name^='monto-movilidad-'],
        input[name^='monto-alimentacion-']
    `);

    montosViajes.forEach(input => {
        const valor = parseFloat(input.value);
        if (!isNaN(valor)) total += valor;
    });

    // 3. Mostrar total en el input final
    const montoTotal = document.getElementById("monto-total");

    // Dar formato al total, con dos decimales
    const totalRedondeado = total.toFixed(2);

    if (montoTotal) montoTotal.value = totalRedondeado;

    // Actualizando el elemento visual de "Monto total con el monto calculado"
    //montoTotal.value = total;

    // Líneas de código para mensaje de advertencia
    const codigoSscc = document.getElementById("codigo_sscc").value;
    const response = await fetch(`anticipos/getSaldoDisponibleTiempoReal?codigo_sscc=${codigoSscc}`);

    const data = await response.json();
    //console.log(`Monto SSCC: ${data}`);
    
    if(montoTotal.value>data){
        //console.log(`No se podrá crear este anticipo el monto total ${montoTotal.value} supera a ${data}`)
        //montoTotal.style.outline = "2px solid red";
        showAlert({
            title: 'Advertencia',
            message: `Esta solicitud no puede ser procesada porque supera el limite del presupuesto asignado para el [SSCC]. Por favor, contacte al área de contabilidad.`,
            type: 'error',
            event: 'error'
        });
    }else{
        //console.log("Todo en orden");
        montoTotal.style.outline = "";
    }
}

async function obtenerCargosDesdeBD() {
    const res = await fetch('tarifario/cargos');
    //console.log(await res.json());
    return await res.json(); // array de objetos {id, nombre}
}

async function obtenerNombresPersonal() {
    const res = await fetch('usuarios/searchByDni');
    //console.log(await res.json());
    return await res.json(); // array de objetos {id, nombre}
}

// Manejar cambio en el select de SCC
const sccSelect = document.getElementById('codigo_scc');
const ssccSelect = document.getElementById('codigo_sscc');

sccSelect.addEventListener('change', async function() {
    const codigoScc = this.value;
    ssccSelect.innerHTML = '<option value="">Seleccione</option>'; // Limpiar opciones
    
    if (codigoScc) {
        try {
            const response = await fetch(`anticipos/getSsccByScc?codigo_scc=${codigoScc}`);
            const ssccs = await response.json();
            
            ssccs.forEach(sscc => {
                const option = document.createElement('option');
                option.value = sscc.codigo;
                option.textContent = `${sscc.codigo} : ${sscc.nombre}`;
                ssccSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error al cargar SSCC:', error);
            ssccSelect.innerHTML = '<option value="">Error al cargar</option>';
        }
    }
});

document.getElementById('addAnticipoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showAlert({
        title: 'Confirmación',
        message: '¿Estás seguro de que deseas registrar este anticipo? Esta acción no se puede deshacer.',
        type: 'confirm',
        event: 'confirm'
    });

    const acceptButton = document.getElementById('custom-alert-btn-aceptar');
    const cancelButton = document.getElementById('custom-alert-btn-cancelar');
    
    acceptButton.onclick = () => {

        const modal = document.getElementById('custom-alert-modal');
        if (modal.style.display !== 'none') {
            const formData = new FormData(this);

            acceptButton.disabled = true;
            cancelButton.disabled = true;

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            fetch('anticipos/add', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                modal.style.display = 'none';
                loadingModal.style.display = 'none';

                showAlert({
                    title: data.success ? 'Aviso Importante' : 'Error',
                    message: data.message,
                    type: data.success ? 'success' : 'error',
                    event: data.success ? 'envio' : ''
                });
            })
            .catch(error => {
                modal.style.display = 'none';
                loadingModal.style.display = 'none';
                showAlert({
                    title: 'Error',
                    message: 'Error al procesar la solicitud.',
                    type: 'error'
                });
                console.error('Error de algo:', error);
            });
        }
    };

    cancelButton.onclick = () => {
        const modal = document.getElementById('custom-alert-modal');
        modal.style.display = 'none';
    };

});

/**********************************************************************/
/********************************Desde aquí inicia todo lo correspondiente a la edición de anticipos */

const editAnticipoModal = document.getElementById("editAnticipoModal");
const editModalTitle = document.getElementById("edit-modal-title");
const editForm = editAnticipoModal.querySelector("form");
const editSubmitButton = editForm.querySelector("button[type='submit']");
const colorModeSwitch = editForm.querySelector("#color_mode");
const editComprasMenoresPanel = document.getElementById("edit-panel-compras-menores");
const editViajesPanel = document.getElementById("edit-panel-viajes");
const editTabsBody = document.getElementById("edit-tabs-body");
const editTabsHeader = document.getElementById("edit-tabs-header");
const editAddGastoBtn = editComprasMenoresPanel.querySelector("#edit-add-gasto-btn");
const editAddTabBtn = editTabsHeader.querySelector("#add-tab");

let gastoCounter = 0;
let editpersonaIndices = [];

async function actualizarTotalGastosEdit(formPrefix = '') {
    let total = 0;

    // Sumar montos de gastos menores
    const montosGastos = document.querySelectorAll(`input[name*='${formPrefix}detalles_gastos'][name$='[importe]']`);
    montosGastos.forEach(input => {
        const valor = parseFloat(input.value);
        const validoInput = input.closest('.gasto-menor').querySelector(`input[name*='[valido]']`);
        if (!isNaN(valor) && (!validoInput || validoInput.value === '1')) total += valor;
    });

    // Sumar montos de viáticos
    const montosViaticos = document.querySelectorAll(`
        input[name^='${formPrefix}monto-hospedaje-'],
        input[name^='${formPrefix}monto-movilidad-'],
        input[name^='${formPrefix}monto-alimentacion-']
    `);
    montosViaticos.forEach(input => {
        const valor = parseFloat(input.value);
        const container = input.closest('.tab-content');
        const validoInput = container.querySelector(`input[name*='[valido]']`);
        const diasInput = input.name.includes('monto-') ? container.querySelector(`input[name='${input.name.replace('monto-', 'dias-')}']`) : null;
        if (!isNaN(valor) && (!validoInput || validoInput.value === '1') && (!diasInput || parseInt(diasInput.value) > 0)) total += valor;
    });

    // Sumar montos de transporte
    const montosTransporte = document.querySelectorAll(`input[name*='${formPrefix}detalles_viajes'][name*='[transporte]'][name$='[monto]']`);
    montosTransporte.forEach(input => {
        const valor = parseFloat(input.value);
        const container = input.closest('.transp-prov-element');
        const validoInput = container.querySelector(`input[name*='[valido]']`);
        if (!isNaN(valor) && (!validoInput || validoInput.value === '1')) {
            total += valor;
            //console.log(`Transporte: ${input.name} = ${valor}`);
        }
    });

    const montoTotalInput = document.querySelector(`#${formPrefix}monto-total`);
    if (montoTotalInput) {
        montoTotalInput.value = total.toFixed(2);
    }

    const ssccSelect = document.querySelector(`#${formPrefix}codigo-sscc`);
    const codigoSscc = ssccSelect.value;
    if (codigoSscc && total > 0) {
        try {
            const response = await fetch(`anticipos/getSaldoDisponibleTiempoReal?codigo_sscc=${encodeURIComponent(codigoSscc)}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            if (data.error) throw new Error(data.error);
            const saldoDisponible = parseFloat(data) || 0;

            if (total > saldoDisponible) {
                //montoTotalInput.style.border = '2px solid red';
                console.log(`No se podrá actualizar este anticipo. El monto total ${total.toFixed(2)} supera el saldo disponible ${saldoDisponible.toFixed(2)}.`);
            } else {
                montoTotalInput.style.border = '';
                //console.log('Monto total dentro del saldo disponible.');
            }
        } catch (error) {
            console.error('Error al validar monto total:', error);
            montoTotalInput.style.border = '';
        }
    } else {
        montoTotalInput.style.border = '';
    }
}




// Cargar SSCC al cambiar SCC
function cargarSscc(selectScc, selectSscc, formPrefix = '') {
    selectScc.addEventListener('change', async function() {
        const codigoScc = this.value;
        selectSscc.innerHTML = '<option value="">Seleccione</option>';
        if (codigoScc) {
            try {
                const response = await fetch(`anticipos/getSsccByScc?codigo_scc=${encodeURIComponent(codigoScc)}`);
                const ssccs = await response.json();
                ssccs.forEach(sscc => {
                    const option = document.createElement('option');
                    option.value = sscc.codigo;
                    option.textContent = `${sscc.codigo} - ${sscc.nombre}`;
                    selectSscc.appendChild(option);
                });
                await actualizarTotalGastosEdit(formPrefix);
            } catch (error) {
                console.error('Error al cargar SSCC:', error);
                selectSscc.innerHTML = '<option value="">Error al cargar</option>';
            }
        }
    });
}

cargarSscc(document.getElementById('edit-codigo-scc'), document.getElementById('edit-codigo-sscc'), 'edit-');

// Validar monto total al cambiar SSCC
document.getElementById('edit-codigo-sscc').addEventListener('change', async function() {
    await actualizarTotalGastosEdit('edit-');
});

// Validar monto total al cambiar inputs de gastos o viáticos
document.addEventListener('input', async function(event) {
    if (
        event.target.matches("input[name*='edit-detalles_viajes']") ||// se agregó a este malcriado y funcionó el cálculo del monto total
        event.target.matches("input[name*='edit-detalles_gastos'][name$='[importe]']") ||
        event.target.matches("input[name^='edit-monto-hospedaje-']") ||
        event.target.matches("input[name^='edit-monto-movilidad-']") ||
        event.target.matches("input[name^='edit-monto-alimentacion-']") ||
        event.target.matches("input[name^='edit-gasto-viaje-']") ||
        event.target.matches("input[name^='edit-dias-hospedaje-']") ||
        event.target.matches("input[name^='edit-dias-movilidad-']") ||
        event.target.matches("input[name^='edit-dias-alimentacion-']")
    ) {
        await actualizarTotalGastosEdit('edit-');
    }
});

// Agregar nuevo gasto en modo edición
editAddGastoBtn.addEventListener('click', function() {
    const gastoDiv = document.createElement('div');
    gastoDiv.className = 'gasto-menor compras-menores-container';
    gastoDiv.innerHTML = `
        <div class="edit-remove-gasto-btn"><i class="fa-regular fa-trash-can"></i></div>
        <input type="hidden" name="edit-detalles_gastos[${gastoCounter}][id]" value="">
        <input type="hidden" name="edit-detalles_gastos[${gastoCounter}][valido]" value="1">
        <div class="modal-element">
            <span class="placeholder">Descripción</span>
            <select name="edit-detalles_gastos[${gastoCounter}][descripcion]" class="descripcion-gasto form-control">
                <option value="">- seleccionar -</option>
                <option value="Combustible">Combustible</option>
                <option value="Peaje">Peaje</option>
                <option value="Parqueo">Parqueo</option>
                <option value="Otros gastos menores">Otros gastos menores</option>
            </select>
        </div>
        <div class="modal-element">
            <span class="placeholder">Motivo</span>
            <input type="text" class="form-control" name="edit-detalles_gastos[${gastoCounter}][motivo]" value="">
        </div>
        <div class="modal-element">
            <span class="placeholder">Moneda</span>
            <select class="form-control" name="edit-detalles_gastos[${gastoCounter}][moneda]">
                <option value="PEN" selected>PEN</option>
            </select>
        </div>
        <div class="modal-element">
            <span class="placeholder">Importe</span>
            <input type="number" class="form-control" name="edit-detalles_gastos[${gastoCounter}][importe]" value="0" min="0" step="0.01">
        </div>
    `;
    editComprasMenoresPanel.insertBefore(gastoDiv, editAddGastoBtn);
    gastoCounter++;
    if(colorModeSwitch){
        toggleEditMode(colorModeSwitch.checked);
    }
    

    const selectDescripcion = gastoDiv.querySelector(".descripcion-gasto");
    const inputMonto = gastoDiv.querySelector("input[name*='[importe]']");
    selectDescripcion.addEventListener("change", () => {
        if (selectDescripcion.value !== "Combustible") {
            inputMonto.setAttribute("max", "400");
            const monto = parseFloat(inputMonto.value);
            if (monto > 400) {
                showAlert({
                    title: 'Advertencia',
                    message: `El monto no puede ser superior a 400 soles para este tipo de gasto.`,
                    type: 'warning',
                    event: 'warning'
                });
                inputMonto.value = "";
                inputMonto.style.outline = "2px solid red";
                inputMonto.focus();
            } else {
                inputMonto.style.outline = "";
            }
        } else {
            inputMonto.removeAttribute("max");
            inputMonto.style.outline = "";
        }
        actualizarTotalGastosEdit('edit-');
    });

    inputMonto.addEventListener("input", () => {
        if (selectDescripcion.value !== "Combustible") {
            const monto = parseFloat(inputMonto.value);
            if (monto > 400) {
                showAlert({
                    title: 'Advertencia',
                    message: `El monto no puede ser superior a 400 soles para este tipo de gasto.`,
                    type: 'warning',
                    event: 'warning'                    
                });
                inputMonto.value = "";
                inputMonto.style.outline = "2px solid red";
                inputMonto.focus();
            } else {
                inputMonto.style.outline = "";
            }
        } else {
            inputMonto.style.outline = "";
        }
        actualizarTotalGastosEdit('edit-');
    });

    // funcionalidad para eliminar las compras menores agregadas
    gastoDiv.querySelector(".edit-remove-gasto-btn").addEventListener("click", () => {
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea eliminar este item, esta acción no se puede deshacer?',
            type: 'confirm',
            event: 'confirm'
        });

        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');

        acceptButton.onclick = () => {
            const validoInput = gastoDiv.querySelector(`input[name*='[valido]']`);
            if (validoInput) {
                validoInput.value = '0';
                gastoDiv.style.display = 'none';
            } else {
                gastoDiv.remove();
            }
            actualizarTotalGastosEdit('edit-');
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
        
        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    });
});

// Manejar doble clic en filas de la tabla de anticipos
document.querySelectorAll('.table.table-hover tbody tr').forEach((e) => {
    e.addEventListener("dblclick", async function() {
        const anticipoId = e.querySelector('td[data-label="ID"]').textContent;
        try {
            const res = await fetch(`anticipos/getAnticipoDetails?id_anticipo=${encodeURIComponent(anticipoId)}`);
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            const data = await res.json();
            if (data.error) throw new Error(data.error);
            //console.log(data);
            await showAnticipoDetails(data);
        } catch (error) {
            console.error('Error al cargar detalles del anticipo:', error);
            //alert('No se pudieron cargar los detalles del anticipo.');
        }
    });
});

// Función para obtener cargos desde el backend
async function obtenerCargosDesdeBD() {
    const res = await fetch('tarifario/cargos');
    return await res.json();
}

// Función para calcular el monto de un concepto
function calcularMontoEdit(index, tipo) {
    const diasInput = document.querySelector(`[name='edit-dias-${tipo}-${index}']`);
    const montoInput = document.querySelector(`[name='edit-monto-${tipo}-${index}']`);
    const container = document.getElementById(`edit-persona-${index}`);
    const validoInput = container.querySelector(`input[name*='[valido]']`);
    if (!validoInput || validoInput.value === '0') return;

    const tarifas = JSON.parse(container.dataset.tarifas || '{}');
    const montoPorDia = tarifas[tipo] || 0;
    const dias = parseInt(diasInput.value) || 0;
    if (!isNaN(dias) && dias >= 0) {
        montoInput.value = (dias * montoPorDia).toFixed(2);
    } else {
        montoInput.value = '';
    }
    actualizarTotalGastosEdit('edit-');
}

// Función para cargar opciones de SCC
async function cargarScc(selectScc, sccSeleccionado) {
    try {
        const response = await fetch('anticipos/getAllScc');
        const sccs = await response.json();
        selectScc.innerHTML = '<option value="">Seleccione</option>';
        sccs.forEach(scc => {
            const option = document.createElement('option');
            option.value = scc.codigo;
            option.textContent = `${scc.codigo} - ${scc.nombre}`;
            if (scc.codigo === sccSeleccionado) {
                option.selected = true;
            }
            selectScc.appendChild(option);
        });
    } catch (error) {
        console.error('Error al cargar SCC:', error);
        selectScc.innerHTML = '<option value="">Error al cargar</option>';
    }
}


// Función para mostrar los detalles del anticipo en el modal de edición

async function showAnticipoDetails(data) {

    editModalTitle.innerText = `Anticipo #${data.id}`;
    editForm.querySelector("#edit-id-anticipo").value = data.id || '';
    editForm.querySelector("#edit-solicitante").value = data.solicitante_nombres || '';
    editForm.querySelector("#edit-dni-solicitante").value = data.dni_solicitante || '';
    editForm.querySelector("#edit-departamento").value = data.departamento_nombre || '';
    editForm.querySelector("#edit-cargo").value = data.cargo || '';
    editForm.querySelector("#edit-nombre-proyecto").value = data.nombre_proyecto || '';
    editForm.querySelector("#edit-motivo-anticipo").value = data.motivo_anticipo || '';
    editForm.querySelector("#edit-fecha-solicitud").value = data.fecha_solicitud || '';
    editForm.querySelector("#edit-fecha-ejecucion").value = data.fecha_inicio || '';
    editForm.querySelector("#edit-fecha-finalizacion").value = data.fecha_fin || '';
    editForm.querySelector("#edit-estado-anticipo").value = data.estado || '';
    editForm.querySelector("#edit-monto-total").value = (parseFloat(data.monto_total_solicitado) || 0).toFixed(2);

    // Probando boton de generar word
    // Crear un form dinámico para enviar y forzar la descarga
    const btnObtenerDocAutorizacion = document.getElementById("get-doc-autorizacion");
    if(btnObtenerDocAutorizacion){
        btnObtenerDocAutorizacion.addEventListener("click", function() {
            
            // Logica para obtener el mes y año actual
            const hoy = new Date();
            const dia = hoy.getDate().toString().padStart(2, "0");
            const meses = [
                "enero", "febrero", "marzo", "abril", "mayo", "junio",
                "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"
            ];
            const mes = meses[hoy.getMonth()];
            const anio = hoy.getFullYear();

            // Logica para formatear la fecha de solicitud
            const fechaOriginal = data.fecha_fin; // "2025-09-02"
            //console.log(fechaOriginal);
            const partes = fechaOriginal.split("-");
            //console.log(partes);

            const fechaSolicitudFormateada = `${partes[2]}/${partes[1]}/${partes[0]}`;

            //Objeto con los valores
            const datos = {
                id_anticipo: data.id,
                nombre: data.solicitante_nombres,
                dni: data.dni_solicitante,
                motivo: data.motivo_anticipo,
                monto: (parseFloat(data.monto_total_solicitado) || 0).toFixed(2),
                fecha_finalizacion: fechaSolicitudFormateada,
                dia_solicitud: dia,
                mes_solicitud: mes,
                nombre2: data.solicitante_nombres,
                dni2: data.dni_solicitante,
                dia_solicitud2: dia,
                mes_solicitud2: (hoy.getMonth()+1).toString().padStart(2, "0"),
                anio_solicitud: anio
            };
            
            const form = document.createElement("form");
            form.method = "POST";
            form.action = "anticipos/getDocAutorizacion";

            const input = document.createElement("input");
            input.type = "hidden";
            input.name = "datos";
            input.value = JSON.stringify(datos);

            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });
    }
    // termina form dinamico para envios

    // adjuntar archivo de autorizacion
    const btnAnadirAutorizacion = document.querySelector('.btn-aniadir-autorizacion');
    const inputArchivo = document.getElementById('edit-archivo-autorizacion');
    const idAnticipoInput = document.getElementById('edit-id-anticipo');

    // Mostrar el input al hacer clic en el botón
    btnAnadirAutorizacion.addEventListener('click', function () {
        inputArchivo.click();
    });

    // Manejar la selección del archivo
    inputArchivo.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {

            showAlert({
                title: 'Confirmación',
                message: `¿Estás seguro de que desea adjuntar el archivo ${file.name}?`,
                type: 'confirm',
                event: 'confirm'
            });

            const acceptButton = document.getElementById('custom-alert-btn-aceptar');
            const cancelButton = document.getElementById('custom-alert-btn-cancelar');
            
            acceptButton.onclick = () => {
                const formData = new FormData();

                acceptButton.disabled = true;
                cancelButton.disabled = true;

                formData.append('id_anticipo', idAnticipoInput.value);
                formData.append('archivo', file);
                formData.append('nombre_original', file.name);

                // Mostrar el modal de carga
                const loadingModal = document.getElementById('loadingModal');
                loadingModal.style.display = 'flex';

                try{
                    fetch('anticipos/guardar_adjunto', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert({
                                title: 'Éxito',
                                message: 'Archivo adjuntado correctamente.',
                                type: 'success',
                                event: 'envio'
                            });
                            editAnticipoModal.style.display = 'none';
                        } else {
                            showAlert({
                                title: 'Error',
                                message: data.error || 'No se pudo adjuntar el archivo.',
                                type: 'error'
                            });
                        }
                    })
                    .catch(error => {
                        showAlert({
                            title: 'Error',
                            message: 'Error al subir el archivo. Intente de nuevo.',
                            type: 'error'
                        });
                        console.error('Error:', error);
                    });
                } catch(error){
                    showAlert({
                        title: 'Error',
                        message: `No se pudo adjuntar el documento. Revise que únicamente sea "pdf, word, jpg o png."`,
                        type: 'error',
                        event: 'envio'
                    });
                } finally {
                    // Ocultar el modal de carga independientemente del resultado
                    loadingModal.style.display = 'none';
                    acceptButton.disabled = false;
                    cancelButton.disabled = false;
                }
            };

            cancelButton.onclick = () => {
                const modal = document.getElementById('custom-alert-modal');
                modal.style.display = 'none';
                inputArchivo.value = '';
            };
        }
    });


    const enlaceDescargaArchivoAdjunto = document.getElementById("edit-enlace-archivo");
    fetch(`anticipos/obtener_adjunto?id_anticipo=${data.id}`)
        .then(response => response.json())
        .then(data => {
            if (data.ruta_archivo) {
                enlaceDescargaArchivoAdjunto.href = data.ruta_archivo;
                enlaceDescargaArchivoAdjunto.target = '_blank';
                enlaceDescargaArchivoAdjunto.querySelector('p').textContent = 'Archivo cargado' || 'File.';
            } else {
                enlaceDescargaArchivoAdjunto.href = 'javascript:void(0)';
                enlaceDescargaArchivoAdjunto.target = '';
                enlaceDescargaArchivoAdjunto.querySelector('p').textContent = 'sin archivo.';
            }
        });

    document.getElementById("editAnticipoModal").setAttribute("data-user-anticipo", data.id_usuario);

    let idUserAnticipo = document.getElementById("editAnticipoModal").getAttribute("data-user-anticipo");;
    let idActualUser = document.getElementById("user-first-info").getAttribute("data-user");

    if(editForm.querySelector("#edit-estado-anticipo").value != 'Nuevo' && editForm.querySelector("#edit-estado-anticipo").value != 'Observado'){
        //console.log(editForm.querySelector("#edit-estado-anticipo").value);
        document.querySelector(".switch").style.display = "none";
    }else{
        //console.log(idActualUser);
        if(idUserAnticipo == idActualUser){
            document.querySelector(".switch").style.display = "block";
        }else{
            document.querySelector(".switch").style.display = "none";
        }
    }

    // Cargar opciones de SCC y preseleccionar
    const editSccSelect = editForm.querySelector("#edit-codigo-scc");
    await cargarScc(editSccSelect, data.scc_codigo);

    // Cargar opciones de SSCC y preseleccionar
    const editSsccSelect = editForm.querySelector("#edit-codigo-sscc");
    editSsccSelect.innerHTML = `<option value="">Seleccione</option>`;
    if (data.scc_codigo) {
        try {
            const response = await fetch(`anticipos/getSsccByScc?codigo_scc=${encodeURIComponent(data.scc_codigo)}`);
            const ssccs = await response.json();
            ssccs.forEach(sscc => {
                const option = document.createElement('option');
                option.value = sscc.codigo;
                option.textContent = `${sscc.codigo} - ${sscc.nombre}`;
                if (sscc.codigo === data.codigo_sscc) {
                    option.selected = true;
                }
                editSsccSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error al cargar SSCC:', error);
            editSsccSelect.innerHTML = `<option value="">Error al cargar</option>`;
        }
    }

    // Limpiar paneles dinámicos
    editComprasMenoresPanel.querySelectorAll('.gasto-menor').forEach(el => el.remove());
    editViajesPanel.querySelectorAll('.tab-content').forEach(el => el.remove());
    editViajesPanel.querySelectorAll('.tab-button:not(.add-tab)').forEach(el => el.remove());
    editpersonaIndices = [];

    // Reiniciar contador de gastos
    gastoCounter = data.detalles_gastos ? data.detalles_gastos.length : 0;

    // Llenar compras menores
    if (data.detalles_gastos && data.detalles_gastos.length > 0) {
        editForm.querySelector("#edit-compras-menores").checked = true;
        editForm.querySelector("#edit-viajes").checked = false;
        editCambioConcepto();
        data.detalles_gastos.forEach((gasto, index) => {
            const gastoDiv = document.createElement('div');
            gastoDiv.className = 'gasto-menor compras-menores-container';
            gastoDiv.innerHTML = `
                <span class="edit-remove-gasto-btn"><i class="fa-regular fa-trash-can"></i></span>
                <input type="hidden" name="edit-detalles_gastos[${index}][id]" value="${gasto.id || ''}">
                <input type="hidden" name="edit-detalles_gastos[${index}][valido]" value="1">
                <div class="modal-element">
                    <span class="placeholder">Descripción</span>
                    <select name="edit-detalles_gastos[${index}][descripcion]" class="descripcion-gasto form-control">
                        <option value="">- seleccionar -</option>
                        <option value="Combustible" ${gasto.descripcion === 'Combustible' ? 'selected' : ''}>Combustible</option>
                        <option value="Peaje" ${gasto.descripcion === 'Peaje' ? 'selected' : ''}>Peaje</option>
                        <option value="Parqueo" ${gasto.descripcion === 'Parqueo' ? 'selected' : ''}>Parqueo</option>
                        <option value="Otros gastos menores" ${gasto.descripcion === 'Otros gastos menores' ? 'selected' : ''}>Otros gastos menores</option>
                    </select>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Motivo</span>
                    <input type="text" class="form-control" name="edit-detalles_gastos[${index}][motivo]" value="${gasto.motivo || ''}" title="${gasto.motivo || ''}">
                </div>
                <div class="modal-element">
                    <span class="placeholder">Moneda</span>
                    <select class="form-control" name="edit-detalles_gastos[${index}][moneda]">
                        <option value="PEN" ${gasto.moneda === 'PEN' ? 'selected' : ''}>PEN</option>
                    </select>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Importe</span>
                    <input type="number" class="form-control" name="edit-detalles_gastos[${index}][importe]" value="${gasto.importe || 0}" min="0" step="0.01">
                </div>
            `;
            editComprasMenoresPanel.insertBefore(gastoDiv, editAddGastoBtn);

            const selectDescripcion = gastoDiv.querySelector(".descripcion-gasto");
            const inputMonto = gastoDiv.querySelector("input[name*='[importe]']");
            selectDescripcion.addEventListener("change", () => {
                if (selectDescripcion.value !== "Combustible") {
                    inputMonto.setAttribute("max", "400");
                    const monto = parseFloat(inputMonto.value);
                    if (monto > 400) {
                        showAlert({
                            title: 'Advertencia',
                            message: `El monto no puede ser superior a 400 soles para este tipo de gasto.`,
                            type: 'warning',
                            event: 'warning'
                        });
                        inputMonto.value = "";
                        inputMonto.style.outline = "2px solid red";
                        inputMonto.focus();
                    } else {
                        inputMonto.style.outline = "";
                    }
                } else {
                    inputMonto.removeAttribute("max");
                    inputMonto.style.outline = "";
                }
                actualizarTotalGastosEdit('edit-');
            });

            inputMonto.addEventListener("input", () => {
                if (selectDescripcion.value !== "Combustible") {
                    const monto = parseFloat(inputMonto.value);
                    if (monto > 400) {
                        showAlert({
                            title: 'Advertencia',
                            message: `El monto no puede ser superior a 400 soles para este tipo de gasto.`,
                            type: 'warning',
                            event: 'warning'
                        });
                        inputMonto.value = "";
                        inputMonto.style.outline = "2px solid red";
                        inputMonto.focus();
                    } else {
                        inputMonto.style.outline = "";
                    }
                } else {
                    inputMonto.style.outline = "";
                }
                actualizarTotalGastosEdit('edit-');
            });

            gastoDiv.querySelector(".edit-remove-gasto-btn").addEventListener("click", () => {
                showAlert({
                    title: 'Confirmación',
                    message: '¿Estás seguro de que desea eliminar este item, esta acción no se puede deshacer?',
                    type: 'confirm',
                    event: 'confirm'
                });
                
                const acceptButton = document.getElementById('custom-alert-btn-aceptar');
                const cancelButton = document.getElementById('custom-alert-btn-cancelar');

                acceptButton.onclick = () => {
                    const validoInput = gastoDiv.querySelector(`input[name*='[valido]']`);
                    if (validoInput) {
                        validoInput.value = '0';
                        gastoDiv.style.display = 'none';
                    } else {
                        gastoDiv.remove();
                    }
                    actualizarTotalGastosEdit('edit-');
                    const modal = document.getElementById('custom-alert-modal');
                    modal.style.display = 'none';
                };

                cancelButton.onclick = () => {
                    const modal = document.getElementById('custom-alert-modal');
                    modal.style.display = 'none';
                };
            });
        });
    } else {
        editForm.querySelector("#edit-viajes").checked = true;
        editForm.querySelector("#edit-compras-menores").checked = false;
        editCambioConcepto();
    }

    // Se obtiene el boton de detalle de viaticos
    const btnDetallesViaticos = document.querySelector(".viaticos-detalles");

    // Llenar viáticos y transporte
    if (data.detalles_viajes && data.detalles_viajes.length > 0) {
        // Se mostrará el botón de detalles de viáticos, únicamene si hay información al respecto
        if(btnDetallesViaticos){
            btnDetallesViaticos.style.display = "block";
        }

        editForm.querySelector("#edit-viajes").checked = true;
        editForm.querySelector("#edit-compras-menores").checked = false;
        editCambioConcepto();
        editpersonaIndices = data.detalles_viajes.map((_, index) => index + 1);

        // Se obteinen los cargos para completarlos dentro del panel de personas
        const cargos = await obtenerCargosDesdeBD();
        let cargoOptions = `<option value="">- Seleccionar cargo -</option>`;
        cargos.forEach(c => {
            cargoOptions += `<option value="${c.id}">${c.nombre}</option>`;
        });

        // Procesar cada persona de manera asíncrona
        await Promise.all(data.detalles_viajes.map(async (viaje, index) => {
            // Crear la pestaña
            const tabId = `edit-persona-${index + 1}`;
            const tabButton = document.createElement('div');
            tabButton.className = 'tab-button';
            tabButton.dataset.tab = tabId;
            tabButton.textContent = `Persona ${index + 1}`;
            tabButton.id = `edit-tab-persona-${index + 1}`;
            editTabsHeader.insertBefore(tabButton, editAddTabBtn);

            // Agregar manejador de clic para la pestaña
            tabButton.addEventListener('click', function() {
                editTabsHeader.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                editTabsBody.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
                this.classList.add('active');
                document.getElementById(this.dataset.tab).style.display = 'block';
            });

            // Obtener tarifas
            let tarifas = {};
            if (viaje.id_cargo) {
                try {
                    const res = await fetch(`tarifario/montosCargo?cargo_id=${viaje.id_cargo}`);
                    const tariffData = await res.json();
                    tariffData.forEach(d => {
                        tarifas[d.concepto.toLowerCase()] = parseFloat(d.monto);
                    });
                } catch (error) {
                    console.error(`Error al cargar tarifas para cargo ${viaje.id_cargo}:`, error);
                }
            }

            // Crear el contenido de la pestaña
            const tabContent = document.createElement('div');
            tabContent.className = 'tab-content';
            tabContent.id = tabId;
            tabContent.dataset.tarifas = JSON.stringify(tarifas);
            tabContent.innerHTML = `
                <input type="hidden" name="edit-detalles_viajes[${index}][valido]" value="${viaje.valido}">
                <input type="hidden" name="edit-detalles_viajes[${index}][id]" value="${viaje.id || ''}">
                <div class="modal-element element-doc-id">
                    <span class="placeholder">Doc. Id ${index + 1}</span>
                    <input type="text" class="form-control" name="edit-detalles_viajes[${index}][doc_identidad]" value="${viaje.doc_identidad || ''}">
                    <span class="lupa" data-index="${index + 1}">
                        <i class="fa-solid fa-xl fa-magnifying-glass"></i>
                    </span>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Nombre</span>
                    <input type="text" class="form-control" name="edit-detalles_viajes[${index}][nombre_persona]" value="${viaje.nombre_persona || ''}">
                </div>
                <div class="modal-element">
                    <span class="placeholder">Cargo</span>
                    <select class="form-control select-cargo" name="edit-detalles_viajes[${index}][id_cargo]" data-index="${index + 1}">
                        ${cargoOptions}
                    </select>
                </div>
                <h4 class="viaje-sub-title">Transporte Provincial
                    <div class="icon-container toggle-icon" data-target=".section-transporte-provincial-${index + 1}">
                        <div class="line horizontal"></div>
                        <div class="line vertical"></div>
                    </div>
                </h4>
                <hr class="separador-viaje-subtitle">
                <div class="viaje-element section-transporte-provincial-${index + 1}" style="display: none;">
                    <div class="transporte-prov-list" id="edit-transp-prov-list-${index + 1}">
                        ${viaje.transporte.map((transporte, tIndex) => `
                            <div class="transp-prov-element">
                                <input type="hidden" name="edit-detalles_viajes[${index}][transporte][${tIndex}][valido]" value="${transporte.valido}">
                                <input type="hidden" name="edit-detalles_viajes[${index}][transporte][${tIndex}][id]" value="${transporte.id}">
                                <div class="edit-remove-transporte-btn"><i class="fa-regular fa-trash-can"></i></div>
                                <div class="med-transporte">
                                    <div>
                                        <input type="radio" name="edit-tipo-transporte-${index + 1}-${tIndex}" id="edit-terrestre-${index + 1}-${tIndex}" value="terrestre" ${transporte.tipo_transporte === 'terrestre' ? 'checked' : ''}>
                                        <label for="edit-terrestre-${index + 1}-${tIndex}">Terrestre</label>
                                    </div>
                                    <div>
                                        <input type="radio" name="edit-tipo-transporte-${index + 1}-${tIndex}" id="edit-aereo-${index + 1}-${tIndex}" value="aereo" ${transporte.tipo_transporte === 'aereo' ? 'checked' : ''}>
                                        <label for="edit-aereo-${index + 1}-${tIndex}">Aéreo</label>
                                    </div>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Ciudad Origen</span>
                                    <input type="text" class="form-control" name="edit-detalles_viajes[${index}][transporte][${tIndex}][ciudad_origen]" value="${transporte.ciudad_origen || ''}">
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Ciudad Destino</span>
                                    <input type="text" class="form-control" name="edit-detalles_viajes[${index}][transporte][${tIndex}][ciudad_destino]" value="${transporte.ciudad_destino || ''}">
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Fecha</span>
                                    <input type="date" class="form-control" name="edit-detalles_viajes[${index}][transporte][${tIndex}][fecha]" value="${transporte.fecha || ''}">
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Gasto</span>
                                    <input type="number" class="form-control gasto-viaje" name="edit-detalles_viajes[${index}][transporte][${tIndex}][monto]" value="${transporte.monto || 0}">
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Moneda</span>
                                    <select class="form-control" name="edit-detalles_viajes[${index}][transporte][${tIndex}][moneda]">
                                        <option value="PEN" ${transporte.moneda === 'PEN' ? 'selected' : ''}>PEN</option>
                                    </select>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="btn edit-adding-transp-provincial" data-persona="${index + 1}" ${index !==0 ? 'style="display: none"' : ''}>Añadir</div>
                </div>
                <h4 class="viaje-sub-title">Hospedaje
                    <div class="icon-container toggle-icon" data-target=".section-hospedaje-${index + 1}">
                        <div class="line horizontal"></div>
                        <div class="line vertical"></div>
                    </div>
                </h4>
                <hr class="separador-viaje-subtitle">
                <div class="viaje-element section-hospedaje-${index + 1}" style="display: none;">
                    <input type="hidden" name="edit-detalles_viajes[${index}][viaticos][hospedaje][id]" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'hospedaje')?.id || ''}">
                    <div class="modal-element">
                        <span class="placeholder">Días</span>
                        <input type="number" class="form-control" name="edit-dias-hospedaje-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'hospedaje')?.dias || 0}" ${index != 0 ? '' : ''} >
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Monto</span>
                        <input type="number" class="form-control monto-hospedaje" name="edit-monto-hospedaje-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'hospedaje')?.monto || 0}" readonly>
                    </div>
                </div>
                <h4 class="viaje-sub-title">Movilidad
                    <div class="icon-container toggle-icon" data-target=".section-movilidad-${index + 1}">
                        <div class="line horizontal"></div>
                        <div class="line vertical"></div>
                    </div>
                </h4>
                <hr class="separador-viaje-subtitle">
                <div class="viaje-element section-movilidad-${index + 1}" style="display: none;">
                    <input type="hidden" name="edit-detalles_viajes[${index}][viaticos][movilidad][id]" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'movilidad')?.id || ''}">
                    <div class="modal-element">
                        <span class="placeholder">Días</span>
                        <input type="number" class="form-control" name="edit-dias-movilidad-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'movilidad')?.dias || 0}" ${index != 0 ? '' : ''}>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Monto</span>
                        <input type="number" class="form-control monto-movilidad" name="edit-monto-movilidad-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'movilidad')?.monto || 0}" readonly>
                    </div>
                </div>
                <h4 class="viaje-sub-title">Alimentación
                    <div class="icon-container toggle-icon" data-target=".section-alimentacion-${index + 1}">
                        <div class="line horizontal"></div>
                        <div class="line vertical"></div>
                    </div>
                </h4>
                <hr class="separador-viaje-subtitle">
                <div class="viaje-element section-alimentacion-${index + 1}" style="display: none;">
                    <input type="hidden" name="edit-detalles_viajes[${index}][viaticos][alimentacion][id]" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'alimentacion')?.id || ''}">
                    <div class="modal-element">
                        <span class="placeholder">Días</span>
                        <input type="number" class="form-control" name="edit-dias-alimentacion-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'alimentacion')?.dias || 0}" ${index != 0 ? '' : ''}>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Monto</span>
                        <input type="number" class="form-control monto-alimentacion" name="edit-monto-alimentacion-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'alimentacion')?.monto || 0}" readonly>
                    </div>
                </div>
                <div class='container-remove-persona'>
                    <div class="btn remove-persona-btn" data-index="${index + 1}">Eliminar</div>
                </div>
            `;
            editTabsBody.appendChild(tabContent);

            const cargoSelect = tabContent.querySelector(`select[name='edit-detalles_viajes[${index}][id_cargo]']`);
            if (viaje.id_cargo) {
                cargoSelect.value = viaje.id_cargo;
            }

            // Forzar cálculo de montos para viáticos
            ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
                calcularMontoEdit(index + 1, tipo);
            });

            cargoSelect.addEventListener("change", async function() {
                const cargoId = this.value;
                const idx = this.dataset.index;
                const container = document.getElementById(`edit-persona-${idx}`);
                if (!cargoId) {
                    ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
                        const diasInput = document.querySelector(`[name='edit-dias-${tipo}-${idx}']`);
                        const montoInput = document.querySelector(`[name='edit-monto-${tipo}-${idx}']`);
                        diasInput.setAttribute('readonly', '');
                        diasInput.value = '';
                        montoInput.value = '';
                    });
                    container.dataset.tarifas = '{}';
                    actualizarTotalGastosEdit('edit-');
                    return;
                }

                const res = await fetch(`tarifario/montosCargo?cargo_id=${cargoId}`);
                const tariffData = await res.json();
                const tarifas = {};
                tariffData.forEach(d => {
                    tarifas[d.concepto.toLowerCase()] = parseFloat(d.monto);
                });
                container.dataset.tarifas = JSON.stringify(tarifas);

                ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
                    const diasInput = document.querySelector(`[name='edit-dias-${tipo}-${idx}']`);
                    //diasInput.removeAttribute('readonly');
                    calcularMontoEdit(idx, tipo);
                });
            });
            // limitación de días a la fecha de inicio y fin - limite dias viaticos
            ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
                const diasInput = tabContent.querySelector(`[name='edit-dias-${tipo}-${index + 1}']`);
                diasInput.addEventListener("input", () => {

                    // --- Validación de días para inputs de edición - modo editar ---

                    const fechaInicio = document.getElementById("edit-fecha-ejecucion")?.value;
                    const fechaFin = document.getElementById("edit-fecha-finalizacion")?.value;

                    if (fechaInicio && fechaFin) {
                        const inicio = new Date(fechaInicio);
                        const fin = new Date(fechaFin);

                        if (fin >= inicio) {
                            const diffDias = Math.floor((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;
                            const valor = parseInt(diasInput.value) || 0;

                            if (valor > diffDias) {
                                alert(`El número máximo de días permitido es ${diffDias}.`);
                                diasInput.value = 0;
                            }
                        }
                    }
    
                    calcularMontoEdit(index + 1, tipo);

                });
            });

            tabContent.querySelector(".lupa").addEventListener("click", async function() {
                const idx = this.dataset.index;
                const docId = document.querySelector(`[name='edit-detalles_viajes[${idx - 1}][doc_identidad]']`).value;
                const inputNombres = document.querySelector(`[name='edit-detalles_viajes[${idx - 1}][nombre_persona]']`);
                const res = await fetch(`usuarios/anticipoBuscarDni?doc-identidad=${docId}`);
                const resData = await res.json();
                if (resData.success) {
                    inputNombres.value = `${resData.data.nombres} ${resData.data.apellidos}`;
                } else {
                    showAlert({
                        title: 'Error',
                        message: 'No se encontraron datos del trabajador.',
                        type: 'error',
                        event: 'error'
                    });
                }
            });
            
            tabContent.querySelectorAll(".edit-remove-transporte-btn").forEach((btn, tIndex) => {
                btn.addEventListener("click", () => {
                    const transporteElement = btn.closest(".transp-prov-element");
                    const validoInput = transporteElement.querySelector(`input[name*='[valido]']`);
                    const personIndex = parseInt(tabContent.id.match(/\d+/)[0]); // Extract person index (e.g., 1 for Person 1)

                    // Show confirmation modal
                    showAlert({
                        title: 'Confirmación',
                        message: '¿Estás seguro de que desea eliminar este item? Esta acción no se puede deshacer.',
                        type: 'confirm',
                        event: 'confirm'
                    });

                    const acceptButton = document.getElementById('custom-alert-btn-aceptar');
                    const cancelButton = document.getElementById('custom-alert-btn-cancelar');

                    acceptButton.onclick = async () => {

                        if (validoInput) {
                            validoInput.value = '0';
                            transporteElement.style.display = 'none';

                            // Synchronize deletion across all other persons
                            if (personIndex === 1) {
                                editpersonaIndices.forEach(idx => {
                                    if (idx !== 1) {
                                        const otherContainer = document.getElementById(`edit-transp-prov-list-${idx}`);
                                        if (otherContainer) {
                                            const otherElement = otherContainer.querySelector(`.transp-prov-element:nth-child(${tIndex + 1})`);
                                            if (otherElement) {
                                                const otherValidoInput = otherElement.querySelector(`input[name*='[valido]']`);
                                                if (otherValidoInput) {
                                                    otherValidoInput.value = '0';
                                                    otherElement.style.display = 'none';
                                                }
                                            }
                                        }
                                    }
                                });
                            }

                            actualizarTotalGastosEdit('edit-');

                            // Submit the form to save the changes
                            const formData = new FormData(editForm);

                            // Show loading modal
                            const loadingModal = document.getElementById('loadingModal');
                            loadingModal.style.display = 'flex';

                            try {
                                const response = await fetch('anticipos/update', {
                                    method: 'POST',
                                    body: formData
                                });
                                const result = await response.json();
                                //console.log(result);
                                if (result.success) {
                                    //console.log("Se eliminó el elemento de transporte");
                                    showAlert({
                                        title: 'Completado',
                                        message: `El anticipo fue actualizado correctamente.`,
                                        type: 'success',
                                        event: 'envio'
                                    });
                                } else {
                                    //console.log("No se pudo actualizar");
                                    showAlert({
                                        title: 'Error',
                                        message: `El anticipo no pudo ser actualizado. "${result.error}".`,
                                        type: 'error',
                                        event: 'envio'
                                    });
                                    // Revert changes if update fails
                                    validoInput.value = '1';
                                    transporteElement.style.display = 'block';
                                    if (personIndex === 1) {
                                        editpersonaIndices.forEach(idx => {
                                            if (idx !== 1) {
                                                const otherContainer = document.getElementById(`edit-transp-prov-list-${idx}`);
                                                if (otherContainer) {
                                                    const otherElement = otherContainer.querySelector(`.transp-prov-element:nth-child(${tIndex + 1})`);
                                                    if (otherElement) {
                                                        const otherValidoInput = otherElement.querySelector(`input[name*='[valido]']`);
                                                        if (otherValidoInput) {
                                                            otherValidoInput.value = '1';
                                                            otherElement.style.display = 'block';
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    }
                                }
                            } catch (error) {
                                showAlert({
                                    title: 'Error',
                                    message: `No se pudo enviar la información del anticipo.`,
                                    type: 'error',
                                    event: 'envio'
                                });
                                // Revert changes on error
                                validoInput.value = '1';
                                transporteElement.style.display = 'block';
                                if (personIndex === 1) {
                                    editpersonaIndices.forEach(idx => {
                                        if (idx !== 1) {
                                            const otherContainer = document.getElementById(`edit-transp-prov-list-${idx}`);
                                            if (otherContainer) {
                                                const otherElement = otherContainer.querySelector(`.transp-prov-element:nth-child(${tIndex + 1})`);
                                                if (otherElement) {
                                                    const otherValidoInput = otherElement.querySelector(`input[name*='[valido]']`);
                                                    if (otherValidoInput) {
                                                        otherValidoInput.value = '1';
                                                        otherElement.style.display = 'block';
                                                    }
                                                }
                                            }
                                        }
                                    });
                                }
                            } finally {
                                loadingModal.style.display = 'none';
                            }
                        }
                        //const modal = document.getElementById('custom-alert-modal');
                        //modal.style.display = 'none';
                        actualizarTotalGastosEdit('edit-');
                    };

                    cancelButton.onclick = () => {
                        const modal = document.getElementById('custom-alert-modal');
                        modal.style.display = 'none';
                    };
                });
            });

            if (index === 0) {
                tabButton.classList.add('active');
                tabContent.style.display = 'block';

                // Sincronización de viáticos
                ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
                    const diasInput = tabContent.querySelector(`[name='edit-dias-${tipo}-${index + 1}']`);
                    const montoInput = tabContent.querySelector(`[name='edit-monto-${tipo}-${index + 1}']`);
                    diasInput.addEventListener("input", () => syncViaticos(tipo));
                    montoInput.addEventListener("input", () => syncViaticos(tipo));
                });

                // Sincronización de transporte provincial
                tabContent.querySelectorAll(".transp-prov-element").forEach((element, tIndex) => {
                    ['ciudad_origen', 'ciudad_destino', 'fecha', 'monto'].forEach(field => {
                        const input = element.querySelector(`[name*='[${tIndex}][${field}]']`);
                        if (input) {
                            input.addEventListener("input", () => syncTransporteProvincial(tIndex));
                        }
                    });
                });
            }

        }));

        // Funciones de sincronización
        function syncViaticos(tipo) {
            const persona1Dias = document.querySelector(`[name='edit-dias-${tipo}-1']`)?.value || 0;
            const persona1Monto = document.querySelector(`[name='edit-monto-${tipo}-1']`)?.value || 0;
            editpersonaIndices.forEach(idx => {
                if (idx !== 1) {
                    const targetDias = document.querySelector(`[name='edit-dias-${tipo}-${idx}']`);
                    const targetMonto = document.querySelector(`[name='edit-monto-${tipo}-${idx}']`);
                    const container = document.getElementById(`edit-persona-${idx}`);
                    const tarifas = JSON.parse(container.dataset.tarifas || '{}');
                    const montoPorDia = tarifas[tipo] || 0;

                    if (targetDias && targetMonto) {
                        targetDias.value = persona1Dias;
                        targetMonto.value = (parseFloat(persona1Dias) * montoPorDia).toFixed(2);
                        calcularMontoEdit(idx, tipo);
                    }
                }
            });
            actualizarTotalGastosEdit('edit-');
        }

        function syncTransporteProvincial(tIndex) {
            const persona1Element = document.querySelector(`#edit-transp-prov-list-1 .transp-prov-element:nth-child(${tIndex + 1})`);
            if (persona1Element) {
                const values = {
                    tipo_transporte: persona1Element.querySelector(`input[name*='[tipo-transporte-1-${tIndex + 1}]']:checked`)?.value || 'terrestre',
                    ciudad_origen: persona1Element.querySelector(`[name*='[ciudad_origen]']`)?.value || '',
                    ciudad_destino: persona1Element.querySelector(`[name*='[ciudad_destino]']`)?.value || '',
                    fecha: persona1Element.querySelector(`[name*='[fecha]']`)?.value || '',
                    monto: persona1Element.querySelector(`[name*='[monto]']`)?.value || ''
                };
                editpersonaIndices.forEach(idx => {
                    if (idx !== 1) {
                        const targetElement = document.querySelector(`#edit-transp-prov-list-${idx} .transp-prov-element:nth-child(${tIndex + 1})`);
                        if (targetElement) {
                            targetElement.querySelector(`input[name*='[tipo-transporte-${idx}-${tIndex + 1}]'][value='${values.tipo_transporte}']`)?.setAttribute('checked', true);
                            targetElement.querySelector(`[name*='[ciudad_origen]']`).value = values.ciudad_origen;
                            targetElement.querySelector(`[name*='[ciudad_destino]']`).value = values.ciudad_destino;
                            targetElement.querySelector(`[name*='[fecha]']`).value = values.fecha;
                            targetElement.querySelector(`[name*='[monto]']`).value = values.monto;
                        }
                    }
                });
                actualizarTotalGastosEdit('edit-');
            }
        }

        // importnate para editar transporte provincial
        // Asegurar sincronización al añadir transporte provincial
        // Delegación para añadir transporte provincial dinámicamente por persona (edit mode)
        editTabsBody.addEventListener("click", function(e) {
            if (e.target.classList.contains("edit-adding-transp-provincial")) {
                const persona = e.target.dataset.persona;
                const container = document.getElementById(`edit-transp-prov-list-${persona}`);
                const index = container.children.length;

                const grupo = document.createElement("div");
                grupo.classList.add("transp-prov-element");
                grupo.innerHTML = `
                    <input type="hidden" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][valido]" value="1">
                    ${persona === "1" ? '<div class="edit-remove-transporte-btn"><i class="fa-regular fa-trash-can"></i></div>' : ''}
                    <div class="med-transporte">
                        <div ${persona === "1" ? '' : 'style="display: none;"'}>
                            <input type="radio" name="edit-tipo-transporte-${persona}-${index}" id="edit-terrestre-${persona}-${index}" value="terrestre" checked>
                            <label for="edit-terrestre-${persona}-${index}">Terrestre</label>
                        </div>
                        <div ${persona === "1" ? '' : 'style="display: none;"'}>
                            <input type="radio" name="edit-tipo-transporte-${persona}-${index}" id="edit-aereo-${persona}-${index}" value="aereo">
                            <label for="edit-aereo-${persona}-${index}">Aéreo</label>
                        </div>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Ciudad Origen</span>
                        <input type="text" class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][ciudad_origen]" ${persona === "1" ? '' : 'readonly'}>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Ciudad Destino</span>
                        <input type="text" class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][ciudad_destino]" ${persona === "1" ? '' : 'readonly'}>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Fecha</span>
                        <input type="date" class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][fecha]" required ${persona === "1" ? '' : 'readonly'}>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Gasto</span>
                        <input type="number" class="form-control gasto-viaje" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][monto]" required ${persona === "1" ? '' : 'readonly'}>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Moneda</span>
                        <select class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][moneda]" ${persona === "1" ? '' : 'readonly'}>
                            <option value="PEN" selected>PEN</option>
                        </select>
                    </div>
                `;
                container.appendChild(grupo);

                
                // Sincronizar para otras personas cuando se edita o se añade información desde persona 1
                if (persona === "1") {
                    editpersonaIndices.forEach(idx => {
                        if (idx !== 1) {
                            const otherContainer = document.getElementById(`edit-transp-prov-list-${idx}`);
                            if (otherContainer) {
                                const otherGrupo = document.createElement("div");
                                otherGrupo.classList.add("transp-prov-element");
                                otherGrupo.innerHTML = `
                                    <input type="hidden" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][valido]" value="1">
                         
                                    <div class="med-transporte">
                                        <div>
                                            <input type="radio" name="edit-tipo-transporte-${idx}-${index}" id="edit-terrestre-${idx}-${index}" value="terrestre" checked readonly>
                                            <label for="edit-terrestre-${idx}-${index}">Terrestre</label>
                                        </div>
                                        <div>
                                            <input type="radio" name="edit-tipo-transporte-${idx}-${index}" id="edit-aereo-${idx}-${index}" value="aereo" readonly>
                                            <label for="edit-aereo-${idx}-${index}">Aéreo</label>
                                        </div>
                                    </div>
                                    <div class="modal-element">
                                        <span class="placeholder">Ciudad Origen</span>
                                        <input type="text" class="form-control" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][ciudad_origen]" readonly>
                                    </div>
                                    <div class="modal-element">
                                        <span class="placeholder">Ciudad Destino</span>
                                        <input type="text" class="form-control" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][ciudad_destino]" readonly>
                                    </div>
                                    <div class="modal-element">
                                        <span class="placeholder">Fecha</span>
                                        <input type="date" class="form-control" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][fecha]" required readonly>
                                    </div>
                                    <div class="modal-element">
                                        <span class="placeholder">Gasto</span>
                                        <input type="number" class="form-control gasto-viaje" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][monto]" required readonly>
                                    </div>
                                    <div class="modal-element">
                                        <span class="placeholder">Moneda</span>
                                        <select class="form-control" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][moneda]">
                                            <option value="PEN" selected>PEN</option>
                                        </select>
                                    </div>
                                `;
                                otherContainer.appendChild(otherGrupo);
                            }
                        }
                    });
                }

                // Sincronizar cambos para persona 1
                if (persona === "1") {
                    const newInputs = {
                        ciudadOrigen: grupo.querySelector(`[name*='[ciudad_origen]']`),
                        ciudadDestino: grupo.querySelector(`[name*='[ciudad_destino]']`),
                        fecha: grupo.querySelector(`[name*='[fecha]']`),
                        monto: grupo.querySelector(`[name*='[monto]']`),
                        tipoTransporte: grupo.querySelector(`input[name="edit-tipo-transporte-${persona}-${index + 1}"]:checked`)
                    };
                    Object.values(newInputs).forEach(input => {
                        if (input) {
                            input.addEventListener("input" || "change", () => {
                                const values = {
                                    tipoTransporte: newInputs.tipoTransporte?.value || 'terrestre',
                                    ciudadOrigen: newInputs.ciudadOrigen?.value || '',
                                    ciudadDestino: newInputs.ciudadDestino?.value || '',
                                    fecha: newInputs.fecha?.value || '',
                                    monto: newInputs.monto?.value || ''
                                };
                                editpersonaIndices.forEach(idx => {
                                    if (idx !== 1) {
                                        const otherItem = document.querySelector(`#edit-transp-prov-list-${idx} .transp-prov-element:nth-child(${index + 1})`);
                                        if (otherItem) {
                                            otherItem.querySelector(`input[name="edit-tipo-transporte-${idx}-${index + 1}"][value="${values.tipoTransporte}"]`)?.setAttribute('checked', true);
                                            otherItem.querySelector(`[name*='[ciudad_origen]']`).value = values.ciudadOrigen;
                                            otherItem.querySelector(`[name*='[ciudad_destino]']`).value = values.ciudadDestino;
                                            otherItem.querySelector(`[name*='[fecha]']`).value = values.fecha;
                                            otherItem.querySelector(`[name*='[monto]']`).value = values.monto;
                                        }
                                    }
                                });
                                actualizarTotalGastosEdit('edit-');
                            });
                        }
                    });
                }

                if (persona === "1") {
                    ['ciudad_origen', 'ciudad_destino', 'fecha', 'monto'].forEach(field => {
                        const input = grupo.querySelector(`[name*='[${index}][${field}]']`);
                        if (input) {
                            input.addEventListener("input", () => syncTransporteProvincial(index));
                        }
                    });
                }

                grupo.querySelector(".gasto-viaje").addEventListener("input", () => actualizarTotalGastosEdit('edit-'));
                grupo.querySelector(".edit-remove-transporte-btn").addEventListener("click", () => {
                    //console.log("a");
                    const validoInput = grupo.querySelector(`input[name*='[valido]']`);
                    if (validoInput) {
                        validoInput.value = '0';
                        grupo.style.display = 'none';
                    } else {
                        grupo.remove();
                    }
                    actualizarTotalGastosEdit('edit-');
                });
            }
        });

        // Ensure edit-adding-transp-provincial is visible only for Person 1
        editTabsBody.addEventListener("click", function(e) {
            const addButton = e.target.closest(".edit-adding-transp-provincial");
            if (addButton) {
                const persona = addButton.dataset.persona;
                addButton.style.display = persona === "1" ? "block" : "none";
            }
        });

        // Actualizar el total después de cargar todos los datos
        actualizarTotalGastosEdit('edit-');
    }else{
        // Se mostrará el botón de detalles de viáticos, únicamene si hay información al respecto
        //console.log("No hay detalles de viaticos");
        if(btnDetallesViaticos){
            btnDetallesViaticos.style.display = "none";
        }
        
    }

    if(colorModeSwitch){
        colorModeSwitch.checked = false;
    }
    
    editSubmitButton.disabled = true;
    editAddGastoBtn.style.display = 'none';
    editAddTabBtn.style.display = 'none';    
    toggleEditMode(false);

    editAnticipoModal.style.display = "block";

    //actualizarTotalGastosEdit('edit-');
    setTimeout(() => actualizarTotalGastosEdit('edit-'), 0);

    // Validaciones para mostrar elementos de cambio de estado en base a estado de un anticipo y del rol del usuario
    const rolUsuario = document.getElementById("user-first-info").getAttribute("data-info");
    const estadoAnticipo = document.getElementById("edit-estado-anticipo").value;
    const containerCambioEstado = document.getElementById("container-cambio-estado");

    //console.log(estadoAnticipo);
    // Lógica para ocultar boton de descarga de archivo de autorización en word, según estado
    const btnEditObtenerDocAutorizacion = document.getElementById("get-doc-autorizacion");
    if(btnEditObtenerDocAutorizacion){
        if(estadoAnticipo != 'Nuevo' && estadoAnticipo != 'Autorizado' && estadoAnticipo != 'Autorizado por Gerencia' && estadoAnticipo!= 'Observado'){
            btnEditObtenerDocAutorizacion.style.display = "none";
            btnEditObtenerDocAutorizacion.style.visibility = "hidden";
        }
    }

    // Lógica para ocultar el boton de adjuntar autorización, según estado
    // const btnAdjuntarDocAutorizacion = document.querySelector(".btn-aniadir-autorizacion");
    // if(btnAdjuntarDocAutorizacion){
    //     console.log(estadoAnticipo);
    //     if(estadoAnticipo != 'Nuevo' && estadoAnticipo != 'Autorizado'){
    //         btnAdjuntarDocAutorizacion.style.display = "none";
    //         btnAdjuntarDocAutorizacion.style.visibility = "hidden";
    //     }else{
    //         btnAdjuntarDocAutorizacion.style.display = "block";
    //         btnAdjuntarDocAutorizacion.style.visibility = "visible";
    //     }
    // }

    // Lógica para ocultar el botn de adjuntar autorización según usuario
    // const dniUsuarioBase = document.getElementById("base-dni-user");
    // if(dniUsuarioBase){
    //     if(dniUsuarioBase.innerText === data.dni_solicitante){
    //         btnAdjuntarDocAutorizacion.style.display = "block";
    //         btnAdjuntarDocAutorizacion.style.visibility = "visible";
    //     }else{
    //         btnAdjuntarDocAutorizacion.style.display = "none";
    //         btnAdjuntarDocAutorizacion.style.visibility = "hidden";
    //     }
    // }
    
    // Probar
    const btnAdjuntarDocAutorizacion = document.querySelector(".btn-aniadir-autorizacion");
    const dniUsuarioBase = document.getElementById("base-dni-user");

    if (btnAdjuntarDocAutorizacion && dniUsuarioBase) {
        // Condición combinada: estado correcto Y usuario correcto
        if ((estadoAnticipo === 'Nuevo' || estadoAnticipo === 'Autorizado' || estadoAnticipo === 'Observado' || estadoAnticipo ==='Autorizado por Gerencia') 
            && dniUsuarioBase.innerText === data.dni_solicitante) {
            
            btnAdjuntarDocAutorizacion.style.display = "block";
            btnAdjuntarDocAutorizacion.style.visibility = "visible";
        } else {
            btnAdjuntarDocAutorizacion.style.display = "none";
            btnAdjuntarDocAutorizacion.style.visibility = "hidden";
        }
    }


    // Lógica para botones por rol de usuario y estado
    const btnAprobar = document.querySelector(".btn-aprobar-anticipo");
    const btnAprobarGerencia = document.querySelector(".btn-aprobar-anticipo-gerencia");
    const btnAprobarTotalmente = document.querySelector(".btn-aprobar-totalmente");
    const btnObservar = document.querySelector(".btn-observar-anticipo");
    const btnAbonar = document.querySelector(".btn-abonar-anticipo");
    const btnAnular = document.querySelector(".btn-anular-anticipo");

    if (btnAprobar) btnAprobar.style.display = "none";
    if (btnAprobarGerencia) btnAprobarGerencia.style.display = "none";
    if (btnAprobarTotalmente) btnAprobarTotalmente.style.display = "none";
    if (btnObservar) btnObservar.style.display = "none";
    if (btnAbonar) btnAbonar.style.display = "none";
    if (btnAnular) btnAbonar.style.display = "block";

    // Ahora aplicamos reglas según rol y estado
    if (rolUsuario == 2) {
        if (estadoAnticipo === "Nuevo" || estadoAnticipo === "Observado") {
            containerCambioEstado.style.display = "flex";
            if (btnAprobar) btnAprobar.style.display = "block";
        } else if (estadoAnticipo === "Autorizado") {
            containerCambioEstado.style.display = "flex";
            if (btnAprobarGerencia) btnAprobarGerencia.style.display = "block";
        } else {
            containerCambioEstado.style.display = "none";
        }
    } else if (rolUsuario == 5) {
        if (estadoAnticipo === "Autorizado por Gerencia") {
            containerCambioEstado.style.display = "flex";
            if (btnAbonar) btnAbonar.style.display = "none";
            if (btnObservar) btnObservar.style.display = "block";
            if (btnAprobarTotalmente) btnAprobarTotalmente.style.display = "block";
        } else if (estadoAnticipo === "Autorizado Totalmente") {
            containerCambioEstado.style.display = "flex";
            if (btnAbonar) btnAbonar.style.display = "block";
            if (btnAnular) btnAnular.style.display = "none";
        } else if(estadoAnticipo==="Abonado" || estadoAnticipo==="Rendido" || estadoAnticipo==="Anulado"){
            containerCambioEstado.style.display = "none";
        } else {
            containerCambioEstado.style.display = "flex";
        }
    } else {
        containerCambioEstado.style.display = "none";
    }

    // Se añade boton de descarga

    const modalFooter = document.getElementById('container-descarga');
    if (modalFooter) {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-descargar-anticipo descargar-anticipo" data-id="${data.id}" data-user="${data.solicitante_nombres.replace(/ /g, '_')}" title="Descargar detalles de anticipo.">
                Detalles <i class="fa-solid fa-download"></i>
            </button>
        `;
    }

    document.querySelector('.descargar-anticipo')?.addEventListener('click', async () => {
        if (!window.XLSX) {
            //alert('Error: The download feature is not available. Please contact support.');
            //console.error('SheetJS not found. Ensure the CDN is included: <script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>');
            console.error('No se pudo descargar el documento excel.');
            return;
        }

        const anticipoId = data.id;
        const userName = data.solicitante_nombres.replace(/ /g, '_');
        const now = new Date();
        const dateStr = `${now.getDate().toString().padStart(2, '0')}${String(now.getMonth() + 1).padStart(2, '0')}${now.getFullYear()}_${now.getHours().toString().padStart(2, '0')}${now.getMinutes().toString().padStart(2, '0')}`;
        const fileName = `${anticipoId}-${userName}-${dateStr}.xlsx`;

        // Prepare anticipo data
        const anticipoData = {
            id_Anticipo: data.id,
            id_Usuario: data.id_usuario,
            Solicitante: data.solicitante_nombres,
            DocIdentidad_Solicitante: data.dni_solicitante,
            Departamento: data.departamento_nombre,
            Codigo_SSCC: data.codigo_sscc,
            Cargo: data.cargo,
            Nombre_de_Proyecto: data.nombre_proyecto,
            Fecha_de_Solicitud: data.fecha_solicitud,
            Motivo_del_Anticipo: data.motivo_anticipo,
            Fecha_de_Ejecucion: data.fecha_inicio,
            Fecha_de_Finalizacion: data.fecha_fin,
            Monto_Total_Solicitado: parseFloat(data.monto_total_solicitado).toFixed(2)
        };

        // Fetch all related data
        let comprasMenoresData = [], viaticosData = [], transporteData = [];
        try {
            // Compras Menores
            const comprasResponse = await fetch(`anticipos/getComprasMenores?anticipo_id=${anticipoId}`);
            const comprasMenores = await comprasResponse.json();
            if (comprasMenores && Array.isArray(comprasMenores.data)) {
                comprasMenoresData = comprasMenores.data.map(item => ({
                    descripcion: item.descripcion,
                    motivo: item.motivo,
                    importe: parseFloat(item.importe).toFixed(2)
                }));
            }

            // Viáticos
            const viaticosResponse = await fetch(`anticipos/getViaticos?anticipo_id=${anticipoId}`);
            const viaticos = await viaticosResponse.json();
            if (viaticos && Array.isArray(viaticos.data)) {
                viaticosData = viaticos.data.map(item => ({
                    doc_identidad: item.doc_identidad,
                    nombre_persona: item.nombre_persona,
                    concepto: item.concepto,
                    dias: parseInt(item.dias),
                    monto: parseFloat(item.monto).toFixed(2),
                    moneda: item.moneda
                }));
            }

            // Transporte Provincial
            const transporteResponse = await fetch(`anticipos/getTransporteProvincial?anticipo_id=${anticipoId}`);
            const transporte = await transporteResponse.json();
            if (transporte && Array.isArray(transporte.data)) {
                transporteData = transporte.data.map(item => ({
                    tipo_transporte: item.tipo_transporte,
                    ciudad_origen: item.ciudad_origen,
                    ciudad_destino: item.ciudad_destino,
                    fecha: item.fecha,
                    monto: parseFloat(item.monto).toFixed(2),
                    moneda: item.moneda,
                    nombre_persona: item.nombre_persona 
                }));
            }
        } catch (error) {
            console.error('Error fetching data:', error);
            alert('Warning: Some data could not be loaded. The file may be incomplete.');
        }

        // Generate Excel using SheetJS
        const { utils, writeFile } = window.XLSX;
        const wb = utils.book_new();

        // Sheet 1: Anticipo Detalles
        const anticipoWs = utils.json_to_sheet([anticipoData], { header: Object.keys(anticipoData) });
        utils.book_append_sheet(wb, anticipoWs, 'Anticipo Detalles');

        // Sheet 2: Compras Menores
        if (comprasMenoresData.length > 0) {
            const comprasMenoresWs = utils.json_to_sheet(comprasMenoresData, { header: ['descripcion', 'motivo', 'importe'] });
            utils.book_append_sheet(wb, comprasMenoresWs, 'Compras Menores');
        }

        // Sheet 3: Viáticos
        if (viaticosData.length > 0) {
            const viaticosWs = utils.json_to_sheet(viaticosData, { header: ['doc_identidad', 'nombre_persona', 'concepto', 'dias', 'monto', 'moneda'] });
            utils.book_append_sheet(wb, viaticosWs, 'Viáticos');
        }

        // Sheet 4: Transporte Provincial
        if (transporteData.length > 0) {
            const transporteWs = utils.json_to_sheet(transporteData, { header: ['tipo_transporte', 'ciudad_origen', 'ciudad_destino', 'fecha', 'monto', 'moneda', 'nombre_persona'] });
            utils.book_append_sheet(wb, transporteWs, 'Transporte Provincial');
        }

        // Write and trigger download
        writeFile(wb, fileName);
    });

    
}

// Agregar nueva persona en modo edición
editAddTabBtn.addEventListener('click', async function() {
    let newIndex = 1;
    while (editpersonaIndices.includes(newIndex)) {
        newIndex++;
    }
    editpersonaIndices.push(newIndex);
    editpersonaIndices.sort((a, b) => a - b);

    // agregado para corregir error de que no se puede agregar nueva persona cuando se edita un anticipo
    const persona1Existe = document.querySelector('[name="edit-dias-hospedaje-1"]') !== null;
    const esBase = (newIndex === 1 || !persona1Existe);

    const newTabBtn = document.createElement("div");
    newTabBtn.className = "tab-button";
    newTabBtn.dataset.tab = `edit-persona-${newIndex}`;
    newTabBtn.textContent = `Persona ${newIndex}`;
    newTabBtn.id = `edit-tab-persona-${newIndex}`;
    editTabsHeader.insertBefore(newTabBtn, editAddTabBtn);

    // Agregar manejador de clic para la nueva pestaña
    newTabBtn.addEventListener('click', function() {
        editTabsHeader.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        editTabsBody.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
        this.classList.add('active');
        document.getElementById(this.dataset.tab).style.display = 'block';
    });

    const cargos = await obtenerCargosDesdeBD();
    let cargoOptions = `<option value="">- Seleccionar cargo -</option>`;
    cargos.forEach(c => {
        cargoOptions += `<option value="${c.id}">${c.nombre}</option>`;
    });

    // Obtener ítems de Transporte Provincial de Persona 1
    const persona1TranspProvList = document.getElementById('edit-transp-prov-list-1');
    let transpProvContent = '';
    if (persona1TranspProvList) {
        const items = persona1TranspProvList.querySelectorAll('.transp-prov-element');
        items.forEach((item, itemIndex) => {

            const oldIndex = 1; // Índice de Persona 1
            const newItemIndex = itemIndex + 1; // Mantener el orden de los ítems
            
            // Obtener valores de Persona 1
            const tipoTransporte = item.querySelector('input[name="edit-tipo-transporte-1-' + itemIndex + '"]:checked')?.value || '';
            const ciudadOrigen = item.querySelector('[name="edit-detalles_viajes[0][transporte]['+itemIndex+'][ciudad_origen]"]')?.value || '';
            const ciudadDestino = item.querySelector('[name="edit-detalles_viajes[0][transporte]['+itemIndex+'][ciudad_destino]"]')?.value || '';
            const fecha = item.querySelector('[name="edit-detalles_viajes[0][transporte]['+itemIndex+'][fecha]"]')?.value || '';
            const monto = item.querySelector('[name="edit-detalles_viajes[0][transporte]['+itemIndex+'][monto]"]')?.value || '';

            const grupo = document.createElement("div");
            grupo.classList.add("transp-prov-element");
            grupo.innerHTML = `
                <input type="hidden" name="edit-detalles_viajes[${newIndex-1}][transporte][${itemIndex}][valido]" value="1">
                <div class="med-transporte">
                    <div>
                        <input type="radio" name="edit-tipo-transporte-${newIndex}-${itemIndex}" id="edit-terrestre-${newIndex}-${itemIndex}" value="terrestre" ${tipoTransporte === 'terrestre' ? 'checked' : ''} ${newIndex === 1 ? '' : 'readonly'}>
                        <label for="edit-terrestre-${newIndex}-${itemIndex}">Terrestre</label>
                    </div>
                    <div>
                        <input type="radio" name="edit-tipo-transporte-${newIndex}-${itemIndex}" id="edit-aereo-${newIndex}-${itemIndex}" value="aereo" ${tipoTransporte === 'aereo' ? 'checked' : ''} ${newIndex === 1 ? '' : 'readonly'}>
                        <label for="edit-aereo-${newIndex}-${itemIndex}">Aéreo</label>
                    </div>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Ciudad Origen</span>
                    <input type="text" class="form-control" name="edit-detalles_viajes[${newIndex-1}][transporte][${itemIndex}][ciudad_origen]" value="${ciudadOrigen}" ${newIndex === 1 ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Ciudad Destino</span>
                    <input type="text" class="form-control" name="edit-detalles_viajes[${newIndex-1}][transporte][${itemIndex}][ciudad_destino]" value="${ciudadDestino}" ${newIndex === 1 ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Fecha</span>
                    <input type="date" class="form-control" name="edit-detalles_viajes[${newIndex-1}][transporte][${itemIndex}][fecha]" value="${fecha}" required ${newIndex === 1 ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Monto</span>
                    <input type="number" class="form-control gasto-viaje" name="edit-detalles_viajes[${newIndex-1}][transporte][${itemIndex}][monto]" value="${monto}" required ${newIndex === 1 ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Moneda</span>
                    <select class="form-control" name="edit-detalles_viajes[${newIndex-1}][transporte][${itemIndex}][moneda]">
                        <option value="PEN" selected>PEN</option>
                    </select>
                </div>
            `;
            transpProvContent += grupo.outerHTML;
        });
    }

    const editNumDiasHospedaje = document.querySelector('[name="edit-dias-hospedaje-1"]')?.value || '';
    const editNumDiasMovilidad = document.querySelector('[name="edit-dias-movilidad-1"]')?.value || '';
    const editNumDiasAlimentacion = document.querySelector('[name="edit-dias-alimentacion-1"]')?.value || '';

    const newTabContent = document.createElement("div");
    newTabContent.className = "tab-content";
    newTabContent.id = `edit-persona-${newIndex}`;
    newTabContent.dataset.tarifas = '{}';
    newTabContent.innerHTML = `
        <input type="hidden" name="edit-detalles_viajes[${newIndex - 1}][valido]" value="1">
        <div class="modal-element element-doc-id">
            <span class="placeholder">Doc. Id ${newIndex}</span>
            <input type="text" class="form-control" name="edit-detalles_viajes[${newIndex - 1}][doc_identidad]" data-index="${newIndex}">
            <span class="lupa" data-index="${newIndex}">
                <i class="fa-solid fa-xl fa-magnifying-glass"></i>
            </span>
        </div>
        <div class="modal-element">
            <span class="placeholder">Nombre</span>
            <input type="text" class="form-control" name="edit-detalles_viajes[${newIndex - 1}][nombre_persona]" data-index="${newIndex}">
        </div>
        <div class="modal-element">
            <span class="placeholder">Cargo</span>
            <select class="form-control select-cargo" name="edit-detalles_viajes[${newIndex - 1}][id_cargo]" data-index="${newIndex}">
                ${cargoOptions}
            </select>
        </div>
        <h4 class="viaje-sub-title">Transporte Provincial
            <div class="icon-container toggle-icon" data-target=".section-transporte-provincial-${newIndex}">
                <div class="line horizontal"></div>
                <div class="line vertical"></div>
            </div>
        </h4>
        <hr class="separador-viaje-subtitle">
        <div class="viaje-element section-transporte-provincial-${newIndex}" style="display: none;">
            <div class="transporte-prov-list" id="edit-transp-prov-list-${newIndex}">${transpProvContent}</div>
            <div class="btn edit-adding-transp-provincial" data-persona="${newIndex}" ${newIndex === 1 ? '' : 'style="display: none;"'}>Añadir</div>
        </div>
        <h4 class="viaje-sub-title">Hospedaje
            <div class="icon-container toggle-icon" data-target=".section-hospedaje-${newIndex}">
                <div class="line horizontal"></div>
                <div class="line vertical"></div>
            </div>
        </h4>
        <hr class="separador-viaje-subtitle">
        <div class="viaje-element section-hospedaje-${newIndex}" style="display: none;">
            <div class="modal-element">
                <span class="placeholder">Días</span>
                <input type="number" class="form-control" name="edit-dias-hospedaje-${newIndex}" value="${editNumDiasHospedaje}" >
            </div>
            <div class="modal-element">
                <span class="placeholder">Monto</span>
                <input type="number" class="form-control monto-hospedaje" name="edit-monto-hospedaje-${newIndex}" readonly>
            </div>
        </div>
        <h4 class="viaje-sub-title">Movilidad
            <div class="icon-container toggle-icon" data-target=".section-movilidad-${newIndex}">
                <div class="line horizontal"></div>
                <div class="line vertical"></div>
            </div>
        </h4>
        <hr class="separador-viaje-subtitle">
        <div class="viaje-element section-movilidad-${newIndex}" style="display: none;">
            <div class="modal-element">
                <span class="placeholder">Días</span>
                <input type="number" class="form-control" name="edit-dias-movilidad-${newIndex}" value="${editNumDiasMovilidad}" >
            </div>
            <div class="modal-element">
                <span class="placeholder">Monto</span>
                <input type="number" class="form-control monto-movilidad" name="edit-monto-movilidad-${newIndex}" readonly>
            </div>
        </div>
        <h4 class="viaje-sub-title">Alimentación
            <div class="icon-container toggle-icon" data-target=".section-alimentacion-${newIndex}">
                <div class="line horizontal"></div>
                <div class="line vertical"></div>
            </div>
        </h4>
        <hr class="separador-viaje-subtitle">
        <div class="viaje-element section-alimentacion-${newIndex}" style="display: none;">
            <div class="modal-element">
                <span class="placeholder">Días</span>
                <input type="number" class="form-control" name="edit-dias-alimentacion-${newIndex}" value="${editNumDiasAlimentacion}" >
            </div>
            <div class="modal-element">
                <span class="placeholder">Monto</span>
                <input type="number" class="form-control monto-alimentacion" name="edit-monto-alimentacion-${newIndex}" readonly>
            </div>
        </div>
        <div class='container-remove-persona'>
            <div class="btn remove-persona-btn" data-index="${newIndex}">Eliminar</div>
        </div>
    `;
    editTabsBody.appendChild(newTabContent);

    newTabContent.querySelector(".select-cargo").addEventListener("change", async function() {
        const cargoId = this.value;
        const idx = this.dataset.index;
        const container = document.getElementById(`edit-persona-${idx}`);
        if (!cargoId) {
            ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
                const diasInput = document.querySelector(`[name='edit-dias-${tipo}-${idx}']`);
                const montoInput = document.querySelector(`[name='edit-monto-${tipo}-${idx}']`);
                diasInput.setAttribute('readonly', '');
                diasInput.value = '';
                montoInput.value = '';
            });
            container.dataset.tarifas = '{}';
            actualizarTotalGastosEdit('edit-');
            return;
        }

        const res = await fetch(`tarifario/montosCargo?cargo_id=${cargoId}`);
        const tariffData = await res.json();
        const tarifas = {};
        tariffData.forEach(d => {
            tarifas[d.concepto.toLowerCase()] = parseFloat(d.monto);
        });
        container.dataset.tarifas = JSON.stringify(tarifas);

        ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
            const diasInput = document.querySelector(`[name='edit-dias-${tipo}-${idx}']`);
            //diasInput.removeAttribute('readonly');
            calcularMontoEdit(idx, tipo);
        });
    });

    ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
        const diasInput = newTabContent.querySelector(`[name='edit-dias-${tipo}-${newIndex}']`);
        diasInput.addEventListener("input", () => {

            // --- Validación de días para inputs de edición - modo editar ---

            const fechaInicio = document.getElementById("edit-fecha-ejecucion")?.value;
            const fechaFin = document.getElementById("edit-fecha-finalizacion")?.value;

            if (fechaInicio && fechaFin) {
                const inicio = new Date(fechaInicio);
                const fin = new Date(fechaFin);

                if (fin >= inicio) {
                    const diffDias = Math.floor((fin - inicio) / (1000 * 60 * 60 * 24)) + 1;
                    const valor = parseInt(diasInput.value) || 0;

                    if (valor > diffDias) {
                        alert(`El número máximo de días permitido es ${diffDias}.`);
                        diasInput.value = 0;
                    }
                }
            }

            calcularMontoEdit(newIndex, tipo);
        });
    });

    newTabContent.querySelector(".lupa").addEventListener("click", async function() {
        const idx = this.dataset.index;
        const docId = document.querySelector(`[name='edit-detalles_viajes[${idx - 1}][doc_identidad]']`).value;
        const inputNombres = document.querySelector(`[name='edit-detalles_viajes[${idx - 1}][nombre_persona]']`);
        const res = await fetch(`usuarios/anticipoBuscarDni?doc-identidad=${docId}`);
        const resData = await res.json();
        if (resData.success) {
            inputNombres.value = `${resData.data.nombres} ${resData.data.apellidos}`;
        } else {
            showAlert({
                title: 'Error',
                message: 'No se encontraron datos del trabajador.',
                type: 'error',
                event: 'error'
            });
        }
    });

    newTabContent.querySelectorAll(".edit-remove-transporte-btn").forEach(btn => {
        //console.log("a");
        btn.addEventListener("click", () => {
            const transporteElement = btn.closest(".transp-prov-element");
            const validoInput = transporteElement.querySelector(`input[name*='[valido]']`);
            if (validoInput) {
                validoInput.value = '0';
                transporteElement.style.display = 'none';
            } else {
                transporteElement.remove();
            }
            actualizarTotalGastosEdit('edit-');
        });
    });

    editTabsHeader.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    editTabsBody.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
    newTabBtn.classList.add('active');
    newTabContent.style.display = 'block';

    actualizarBotonesEliminarEdit();

    editTabsBody.addEventListener("click", function(e) {

        if (e.target.classList.contains("edit-adding-transp-provincial")) {
    
            const persona = e.target.dataset.persona;
            const container = document.getElementById(`edit-transp-prov-list-${persona}`);
            const index = container.children.length;

            const grupo = document.createElement("div");
            grupo.classList.add("transp-prov-element");
            grupo.innerHTML = `
                <input type="hidden" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][valido]" value="1">
                ${persona === "1" ? '<div class="edit-remove-transporte-btn"><i class="fa-regular fa-trash-can"></i></div>' : ''}
                <div class="med-transporte">
                    <div ${persona === "1" ? '' : 'style="display: none;"'}>
                        <input type="radio" name="edit-tipo-transporte-${persona}-${index}" id="edit-terrestre-${persona}-${index}" value="terrestre" checked>
                        <label for="edit-terrestre-${persona}-${index}">Terrestre</label>
                    </div>
                    <div ${persona === "1" ? '' : 'style="display: none;"'}>
                        <input type="radio" name="edit-tipo-transporte-${persona}-${index}" id="edit-aereo-${persona}-${index}" value="aereo">
                        <label for="edit-aereo-${persona}-${index}">Aéreo</label>
                    </div>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Ciudad Origen</span>
                    <input type="text" class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][ciudad_origen]" ${persona === "1" ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Ciudad Destino</span>
                    <input type="text" class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][ciudad_destino]" ${persona === "1" ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Fecha</span>
                    <input type="date" class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][fecha]" required ${persona === "1" ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Gasto</span>
                    <input type="number" class="form-control gasto-viaje" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][monto]" required ${persona === "1" ? '' : 'readonly'}>
                </div>
                <div class="modal-element">
                    <span class="placeholder">Moneda</span>
                    <select class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][moneda]" ${persona === "1" ? '' : 'readonly'}>
                        <option value="PEN" selected>PEN</option>
                    </select>
                </div>
            `;
            container.appendChild(grupo);

            
            // Sincronizar para otras personas cuando se edita o se añade información desde persona 1
            if (persona === "1") {
                editpersonaIndices.forEach(idx => {
                    if (idx !== 1) {
                        const otherContainer = document.getElementById(`edit-transp-prov-list-${idx}`);
                        if (otherContainer) {
                            const otherGrupo = document.createElement("div");
                            otherGrupo.classList.add("transp-prov-element");
                            otherGrupo.innerHTML = `
                                <input type="hidden" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][valido]" value="1">
                        
                                <div class="med-transporte">
                                    <div>
                                        <input type="radio" name="edit-tipo-transporte-${idx}-${index}" id="edit-terrestre-${idx}-${index}" value="terrestre" checked readonly>
                                        <label for="edit-terrestre-${idx}-${index}">Terrestre</label>
                                    </div>
                                    <div>
                                        <input type="radio" name="edit-tipo-transporte-${idx}-${index}" id="edit-aereo-${idx}-${index}" value="aereo" readonly>
                                        <label for="edit-aereo-${idx}-${index}">Aéreo</label>
                                    </div>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Ciudad Origen</span>
                                    <input type="text" class="form-control" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][ciudad_origen]" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Ciudad Destino</span>
                                    <input type="text" class="form-control" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][ciudad_destino]" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Fecha</span>
                                    <input type="date" class="form-control" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][fecha]" required readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Gasto</span>
                                    <input type="number" class="form-control gasto-viaje" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][monto]" required readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Moneda</span>
                                    <select class="form-control" name="edit-detalles_viajes[${idx - 1}][transporte][${index}][moneda]">
                                        <option value="PEN" selected>PEN</option>
                                    </select>
                                </div>
                            `;
                            otherContainer.appendChild(otherGrupo);
                        }
                    }
                });
            }

            // Sincronizar cambos para persona 1
            if (persona === "1") {
                const newInputs = {
                    ciudadOrigen: grupo.querySelector(`[name*='[ciudad_origen]']`),
                    ciudadDestino: grupo.querySelector(`[name*='[ciudad_destino]']`),
                    fecha: grupo.querySelector(`[name*='[fecha]']`),
                    monto: grupo.querySelector(`[name*='[monto]']`),
                    tipoTransporte: grupo.querySelector(`input[name="edit-tipo-transporte-${persona}-${index + 1}"]:checked`)
                };
                Object.values(newInputs).forEach(input => {
                    if (input) {
                        input.addEventListener("input" || "change", () => {
                            const values = {
                                tipoTransporte: newInputs.tipoTransporte?.value || 'terrestre',
                                ciudadOrigen: newInputs.ciudadOrigen?.value || '',
                                ciudadDestino: newInputs.ciudadDestino?.value || '',
                                fecha: newInputs.fecha?.value || '',
                                monto: newInputs.monto?.value || ''
                            };
                            editpersonaIndices.forEach(idx => {
                                if (idx !== 1) {
                                    const otherItem = document.querySelector(`#edit-transp-prov-list-${idx} .transp-prov-element:nth-child(${index + 1})`);
                                    if (otherItem) {
                                        otherItem.querySelector(`input[name="edit-tipo-transporte-${idx}-${index + 1}"][value="${values.tipoTransporte}"]`)?.setAttribute('checked', true);
                                        otherItem.querySelector(`[name*='[ciudad_origen]']`).value = values.ciudadOrigen;
                                        otherItem.querySelector(`[name*='[ciudad_destino]']`).value = values.ciudadDestino;
                                        otherItem.querySelector(`[name*='[fecha]']`).value = values.fecha;
                                        otherItem.querySelector(`[name*='[monto]']`).value = values.monto;
                                    }
                                }
                            });
                            actualizarTotalGastosEdit('edit-');
                        });
                    }
                });
            }

            if (persona === "1") {
                ['ciudad_origen', 'ciudad_destino', 'fecha', 'monto'].forEach(field => {
                    const input = grupo.querySelector(`[name*='[${index}][${field}]']`);
                    if (input) {
                        input.addEventListener("input", () => syncTransporteProvincial(index));
                    }
                });
            }

            grupo.querySelector(".gasto-viaje").addEventListener("input", () => actualizarTotalGastosEdit('edit-'));
            grupo.querySelector(".edit-remove-transporte-btn").addEventListener("click", () => {
                //console.log("a");
                const validoInput = grupo.querySelector(`input[name*='[valido]']`);
                if (validoInput) {
                    validoInput.value = '0';
                    grupo.style.display = 'none';
                } else {
                    grupo.remove();
                }
                actualizarTotalGastosEdit('edit-');
            });
        }
    });
});


// Manejando la eliminación de personas - Edit Panel
editTabsBody.addEventListener("click", function(e) {
    if (e.target.classList.contains("remove-persona-btn")) {
        
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea eliminar este item, esta acción no se puede deshacer?',
            type: 'confirm',
            event: 'confirm'
        });

        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');

        acceptButton.onclick = () => {
            const index = parseInt(e.target.dataset.index, 10);
            const tab = document.getElementById(`edit-tab-persona-${index}`);
            const content = document.getElementById(`edit-persona-${index}`);
            const validoInput = content.querySelector(`input[name*='[valido]']`);

            if (validoInput) {
                validoInput.value = '0';
                // Marcar transportes como inactivos
                content.querySelectorAll('input[name*="transporte"][name*="valido"]').forEach(input => {
                    input.value = '0';
                });
                // Resetear días y montos de viáticos
                ['hospedaje', 'movilidad', 'alimentacion'].forEach(tipo => {
                    const diasInput = content.querySelector(`input[name="edit-dias-${tipo}-${index}"]`);
                    const montoInput = content.querySelector(`input[name="edit-monto-${tipo}-${index}"]`);
                    if (diasInput) diasInput.value = '0';
                    if (montoInput) montoInput.value = '0';
                });
                content.style.display = 'none';
                tab.style.display = 'none';
            } else {
                content.remove();
                tab.remove();
            }
            editpersonaIndices = editpersonaIndices.filter(i => i !== index);
            const firstTab = editTabsHeader.querySelector(".tab-button[data-tab]:not([style*='display: none'])");
            if (firstTab) {
                firstTab.classList.add('active');
                document.getElementById(firstTab.dataset.tab).style.display = 'block';
            }
            actualizarBotonesEliminarEdit();
            actualizarTotalGastosEdit('edit-');

            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };

        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    }
});

// Corregir toggle de secciones
editTabsBody.addEventListener("click", function(e) {
    const icon = e.target.closest(".toggle-icon");
    if (!icon) return;

    const targetSelector = icon.dataset.target;
    const container = icon.closest(".tab-content");
    if (!container) return;

    const targetSection = container.querySelector(targetSelector);
    const isVisible = targetSection && targetSection.style.display === "block";

    // Ocultar todas las secciones dentro de la pestaña actual
    container.querySelectorAll(".viaje-element").forEach(section => {
        section.style.display = "none";
    });

    // Quitar clase "minus" de todos los íconos en la pestaña actual
    container.querySelectorAll(".toggle-icon").forEach(ic => ic.classList.remove("minus"));

    // Mostrar la sección seleccionada si no estaba visible
    if (isVisible && targetSection) {
        targetSection.style.display = "block";
        icon.classList.add("minus");
    }
});

function toggleEditMode(isEditMode) {
    const isEditable = ['Nuevo', 'Observado'].includes(editForm.querySelector("#edit-estado-anticipo").value);
    const inputs = editForm.querySelectorAll('input:not([type="radio"]):not([type="checkbox"]), select');

    inputs.forEach(input => {
        // Campos siempre de solo lectura
        if (['edit-solicitante', 'edit-dni-solicitante', 'edit-departamento', 'edit-cargo', 'edit-fecha-solicitud', 'edit-nombre-proyecto', 'edit-monto-total'].includes(input.id)) {
            input.readOnly = true;
        }

        if(input.name.includes('edit-codigo-scc') || input.name.includes('edit-codigo-sscc') || input.name.includes('edit-motivo-anticipo')){

            input.disabled = true;
            input.readOnly = true;

            if (isEditMode && isEditable) {
                input.disabled = false;
                input.readOnly = false;
            }
        }

        if(input.name.includes('edit-fecha-ejecucion') || input.name.includes('edit-fecha-finalizacion')){

            input.disabled = true;
 
            if (isEditMode && isEditable) {
                input.disabled = false;
            }
        }

        if(input.name.includes('edit-detalles_gastos')){
            input.readOnly = true;
            input.disabled = true;
            if (isEditMode && isEditable) {
                input.readOnly = false;
                input.disabled = false;
            }
        }

        // Campos de detalles_gastos y detalles_viajes
        else if (input.name.includes('edit-detalles_viajes')) {
            // Por defecto, todos en readonly
            input.readOnly = true; //Considerar que en este apartado, cuando se intenta editar un valor de "Compras menores, no se está permitiendo" 

            if (isEditMode && isEditable) {
                // Persona 1 (índice 0) puede editar todo
                const match = input.name.match(/edit-detalles_viajes\[(\d+)\]/);
                if (match && parseInt(match[1]) === 0) {
                    input.readOnly = false;
                } 
                // Otras personas (índices > 0) solo editan doc_identidad, nombre_persona, id_cargo
                else if (match) {
                    const index = parseInt(match[1]);
                    const fieldMatch = input.name.match(/edit-detalles_viajes\[\d+\]\[(doc_identidad|nombre_persona|id_cargo)\]/);

                    if (fieldMatch && index > 0) {
                        input.readOnly = false;
                    }
                    // Transporte, hospedaje, movilidad y alimentación readonly para otras personas
                    if (index > 0 && (input.name.includes('edit-dias-hospedaje') || input.name.includes('edit-monto-hospedaje'))) {
                        input.readOnly = true;
                    }
                }
            }
        }
    });

    // Controles de edición
    editSubmitButton.disabled = !isEditMode || !isEditable;
    editSubmitButton.style.display = isEditMode && isEditable ? 'block' : 'none';
    editAddGastoBtn.style.display = isEditMode && isEditable ? 'block' : 'none';
    editAddTabBtn.style.display = isEditMode && isEditable ? 'block' : 'none';

    // Mostrar/Ocultar botones de eliminación y añadir transporte solo para Persona 1
    document.querySelectorAll(".edit-remove-gasto-btn").forEach(e => {
        e.style.display = isEditMode && isEditable ? 'block' : 'none';
    });
    document.querySelectorAll(".edit-remove-transporte-btn").forEach(e => {
        e.style.display = (isEditMode && isEditable && e.closest('#edit-transp-prov-list-1')) ? 'block' : 'none';
    });
    document.querySelectorAll(".container-remove-persona").forEach(e => {
        e.style.display = isEditMode && isEditable ? 'flex' : 'none';
    });
    document.querySelectorAll(".edit-adding-transp-provincial").forEach(e => {
        e.style.display = (isEditMode && isEditable && e.dataset.persona === '1') ? 'block' : 'none';
    });
}

// Manejar el interruptor de modo, si existe
if(colorModeSwitch){
    colorModeSwitch.addEventListener('change', function() {
        toggleEditMode(this.checked);
    });
}


// Selección y cambio de vista en sección de Concepto
const editOpcionesConcepto = document.querySelectorAll("input[name='edit-concepto']");
function editCambioConcepto() {
    if (document.getElementById("edit-compras-menores").checked) {
        editComprasMenoresPanel.style.display = "block";
        editViajesPanel.style.display = "none";
    } else if (document.getElementById("edit-viajes").checked) {
        editComprasMenoresPanel.style.display = "none";
        editViajesPanel.style.display = "block";
    }
}

editOpcionesConcepto.forEach(radioBtn => {
    radioBtn.addEventListener("change", editCambioConcepto);
});

// Cerrar modal
editAnticipoModal.querySelector('.btn-close-modal').addEventListener('click', function() {
    editAnticipoModal.style.display = 'none';
    editForm.reset();
    editComprasMenoresPanel.querySelectorAll('.gasto-menor').forEach(el => el.remove());
    editTabsBody.innerHTML = '';
    editTabsHeader.querySelectorAll('.tab-button:not(.add-tab)').forEach(el => el.remove());
    editForm.querySelector("#edit-compras-menores").checked = true;
    editForm.querySelector("#edit-viajes").checked = false;
    editCambioConcepto();
    gastoCounter = 0;
    editpersonaIndices = [];
});

// Manejar envío del formulario
editForm.addEventListener('submit', async function(event) {
    event.preventDefault();
    showAlert({
        title: 'Confirmación',
        message: '¿Estás seguro de que desea culminar con la actualización de este anticipo?',
        type: 'confirm',
        event: 'confirm'
    });

    const acceptButton = document.getElementById('custom-alert-btn-aceptar');
    const cancelButton = document.getElementById('custom-alert-btn-cancelar');

    acceptButton.onclick = async () => {

        const formData = new FormData(this);

        // Mostrar el modal de carga
        const loadingModal = document.getElementById('loadingModal');
        loadingModal.style.display = 'flex';

        try {
            const response = await fetch('anticipos/update', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                showAlert({
                    title: 'Completado',
                    message: `El anticipo fue actualizado correctamente.`,
                    type: 'success',
                    event: 'envio'
                });
            } else {
                showAlert({
                    title: 'Error',
                    message: `El anticipo no pudo ser actualizado. "${result.error}".`,
                    type: 'error',
                    event: 'envio'
                });
            }
        } catch (error) {
            showAlert({
                title: 'Error',
                message: `No se pudo enviar la información del anticipo.`,
                type: 'error',
                event: 'envio'
            });
        } finally{
            // Ocultar el modal de carga independientemente del resultado
            loadingModal.style.display = 'none';
            acceptButton.disabled = false;
            cancelButton.disabled = false;
        }
    };

    cancelButton.onclick = () => {
        const modal = document.getElementById('custom-alert-modal');
        modal.style.display = 'none';
    };
});

// Actualizar visibilidad de botones de eliminación de personas
function actualizarBotonesEliminarEdit() {
    editTabsBody.querySelectorAll(".remove-persona-btn").forEach(btn => btn.style.display = "none");
    const max = Math.max(...editpersonaIndices);
    const btn = document.querySelector(`#edit-persona-${max} .remove-persona-btn`);
    if (btn) btn.style.display = "inline-block";
}

// Autorizacion 1 de un anticipo
const btnAutorizarAprobador = document.querySelector(".btn-aprobar-anticipo");
if(btnAutorizarAprobador){
    btnAutorizarAprobador.addEventListener("click", async function(e){
        // estos valores serán usados también para la notificación por correo electrónico
        let idAnticipo = document.getElementById("edit-id-anticipo").value;
        let dniSolicitante = document.getElementById("edit-dni-solicitante").value;
        let solicitanteNombre = document.getElementById("edit-solicitante").value;
        let sscc = document.getElementById("edit-codigo-sscc").value;
        let nombreProyecto = document.getElementById("edit-nombre-proyecto").value;
        let motivoAnticipo = document.getElementById("edit-motivo-anticipo").value;
        let montoTotal = document.getElementById("edit-monto-total").value;

        //let userId = btnAutorizarAprobador.getAttribute("data-aprobador");
        
        e.preventDefault();
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que deseas autorizar este anticipo? Esta acción no se puede deshacer.',
            type: 'confirm',
            event: 'confirm'
        });
        
        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        
        acceptButton.onclick = async () => {

            // const comentario = document.getElementById('custom-alert-comentario').value;
            const formData = new FormData();

            acceptButton.disabled = true;
            cancelButton.disabled = true;

            formData.append("id", idAnticipo);
            formData.append("dniSolicitante", dniSolicitante);
            formData.append("comentario", 'Anticipo Aprobado');
            formData.append("sscc", sscc);
            formData.append("solicitanteNombre", solicitanteNombre);
            formData.append("nombreProyecto", nombreProyecto);
            formData.append("motivoAnticipo", motivoAnticipo);
            formData.append("montoTotal", montoTotal);

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            try {
                const response = await fetch('anticipos/autorizar', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    showAlert({
                        title: 'Completado',
                        message: `El anticipo fue autorizado correctamente.`,
                        type: 'success',
                        event: 'envio'
                    });
                    editAnticipoModal.style.display = 'none';
                } else {
                    showAlert({
                        title: 'Error',
                        message: `El anticipo no pudo ser autorizado. "${result.error}".`,
                        type: 'error',
                        event: 'envio'
                    });
                }
            } catch (error) {
                showAlert({
                    title: 'Error',
                    message: `No se pudo enviar la información del anticipo.`,
                    type: 'error',
                    event: 'envio'
                });
            } finally {
                // Ocultar el modal de carga independientemente del resultado
                loadingModal.style.display = 'none';
                acceptButton.disabled = false;
                cancelButton.disabled = false;
            }
        };

        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    })
}

// Autorización de  gerencia para un anticipo
const btnAutorizarAprobadorGerencia = document.querySelector(".btn-aprobar-anticipo-gerencia");
if(btnAutorizarAprobadorGerencia){
    btnAutorizarAprobadorGerencia.addEventListener("click", async function(e){
        // estos valores serán usados también para la notificación por correo electrónico
        let idAnticipo = document.getElementById("edit-id-anticipo").value;
        let dniSolicitante = document.getElementById("edit-dni-solicitante").value;
        let solicitanteNombre = document.getElementById("edit-solicitante").value;
        let sscc = document.getElementById("edit-codigo-sscc").value;
        let nombreProyecto = document.getElementById("edit-nombre-proyecto").value;
        let motivoAnticipo = document.getElementById("edit-motivo-anticipo").value;
        let montoTotal = document.getElementById("edit-monto-total").value;

        //let userId = btnAutorizarAprobador.getAttribute("data-aprobador");
        
        e.preventDefault();
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que deseas autorizar este anticipo? Esta acción no se puede deshacer.',
            type: 'confirm',
            event: 'confirm'
        });
        
        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        
        acceptButton.onclick = async () => {

            // const comentario = document.getElementById('custom-alert-comentario').value;
            const formData = new FormData();

            acceptButton.disabled = true;
            cancelButton.disabled = true;

            formData.append("id", idAnticipo);
            formData.append("dniSolicitante", dniSolicitante);
            formData.append("comentario", 'Anticipo Autorizado por gerencia');
            formData.append("sscc", sscc);
            formData.append("solicitanteNombre", solicitanteNombre);
            formData.append("nombreProyecto", nombreProyecto);
            formData.append("motivoAnticipo", motivoAnticipo);
            formData.append("montoTotal", montoTotal);

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            try {
                const response = await fetch('anticipos/autorizacionGerencia', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    showAlert({
                        title: 'Completado',
                        message: `El anticipo fue autorizado por gerencia correctamente.`,
                        type: 'success',
                        event: 'envio'
                    });
                    editAnticipoModal.style.display = 'none';
                } else {
                    showAlert({
                        title: 'Error',
                        message: `El anticipo no pudo ser autorizado. "${result.error}".`,
                        type: 'error',
                        event: 'envio'
                    });
                }
            } catch (error) {
                showAlert({
                    title: 'Error',
                    message: `No se pudo enviar la información del anticipo.`,
                    type: 'error',
                    event: 'envio'
                });
            } finally {
                // Ocultar el modal de carga independientemente del resultado
                loadingModal.style.display = 'none';
                acceptButton.disabled = false;
                cancelButton.disabled = false;
            }
        };

        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    })
}

// Autorizacion total por parte de tesorería de un anticipo
const btnAutorizarTotalmente = document.querySelector(".btn-aprobar-totalmente");
if(btnAutorizarTotalmente){
    btnAutorizarTotalmente.addEventListener("click", async function(e){
        let idAnticipo = document.getElementById("edit-id-anticipo").value;
        //let userId = btnAutorizarAprobador.getAttribute("data-aprobador");
        let dniSolicitante = document.getElementById("edit-dni-solicitante").value;
        let solicitanteNombre = document.getElementById("edit-solicitante").value;
        let sscc = document.getElementById("edit-codigo-sscc").value;
        let nombreProyecto = document.getElementById("edit-nombre-proyecto").value;
        let motivoAnticipo = document.getElementById("edit-motivo-anticipo").value;
        let montoTotal = document.getElementById("edit-monto-total").value;
        

        e.preventDefault();
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea autorizar totalmente este anticipo? Esta acción no se puede deshacer.',
            type: 'confirm',
            event: 'confirm'
        });
        
        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');

        acceptButton.onclick = async () => {

            const formData = new FormData();

            acceptButton.disabled = true;
            cancelButton.disabled = true;

            formData.append("id", idAnticipo);
            formData.append("dniSolicitante", dniSolicitante);
            formData.append("solicitanteNombre", solicitanteNombre);
            formData.append("sscc", sscc);
            formData.append("nombreProyecto", nombreProyecto);
            formData.append("motivoAnticipo", motivoAnticipo);
            formData.append("montoTotal", montoTotal);

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            try {
                const response = await fetch('anticipos/autorizarTotalmente', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    showAlert({
                        title: 'Completado',
                        message: `El anticipo fue autorizado correctamente.`,
                        type: 'success',
                        event: 'envio'
                    });
                    editAnticipoModal.style.display = 'none';
                } else {
                    showAlert({
                        title: 'Error',
                        message: `El anticipo no pudo ser autorizado. "${result.error}".`,
                        type: 'error',
                        event: 'envio'
                    });
                }
            } catch (error) {
                showAlert({
                    title: 'Error',
                    message: `No se pudo enviar la información del anticipo.`,
                    type: 'error',
                    event: 'envio'
                });
            } finally {
                // Ocultar el modal de carga independientemente del resultado
                loadingModal.style.display = 'none';
                acceptButton.disabled = false;
                cancelButton.disabled = false;
            }
        };

        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    })
}

// Observar Anticipo
const btnObservarAnticipo = document.querySelector(".btn-observar-anticipo");
if(btnObservarAnticipo){
    btnObservarAnticipo.addEventListener("click", async function(e){
        let idAnticipo = document.getElementById("edit-id-anticipo").value;
        //let userId = btnAutorizarAprobador.getAttribute("data-aprobador");

        let dniSolicitante = document.getElementById("edit-dni-solicitante").value;
        let solicitanteNombre = document.getElementById("edit-solicitante").value;
        let sscc = document.getElementById("edit-codigo-sscc").value;
        let nombreProyecto = document.getElementById("edit-nombre-proyecto").value;
        let motivoAnticipo = document.getElementById("edit-motivo-anticipo").value;
        let montoTotal = document.getElementById("edit-monto-total").value;
        
        e.preventDefault();
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea marcar este anticipo como observado?',
            type: 'confirm',
            event: 'confirm-comment'
        });

        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        
        acceptButton.onclick = async () => {

            const comentario = document.getElementById('custom-alert-comentario').value.trim();

            // Si el boton se encuentra desactivado, no se realizará nada
            if (acceptButton.disabled) {
                return;
            }

            const formData = new FormData();

            acceptButton.disabled = true;
            cancelButton.disabled = true;

            formData.append("id", idAnticipo);
            formData.append("comentario", comentario);
            formData.append("dniSolicitante", dniSolicitante);
            formData.append("solicitanteNombre", solicitanteNombre);
            formData.append("sscc", sscc);
            formData.append("nombreProyecto", nombreProyecto);
            formData.append("motivoAnticipo", motivoAnticipo);
            formData.append("montoTotal", montoTotal);

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            try {
                const response = await fetch('anticipos/observarAnticipo', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    showAlert({
                        title: 'Completado',
                        message: `El anticipo fue marcado como observado.`,
                        type: 'success',
                        event: 'envio'
                    });
                    editAnticipoModal.style.display = 'none';
                } else {
                    showAlert({
                        title: 'Error',
                        message: `El anticipo no pudo ser marcado como observado. "${result.error}".`,
                        type: 'error',
                        event: 'envio'
                    });
                }
            } catch (error) {
                showAlert({
                    title: 'Error',
                    message: `No se pudo enviar la información del anticipo.`,
                    type: 'error',
                    event: 'envio'
                });
            } finally {
                // Ocultar el modal de carga independientemente del resultado
                loadingModal.style.display = 'none';
                acceptButton.disabled = false;
                cancelButton.disabled = false;
            }
        };

        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
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
    })
}

// Anular Anticipo
const btnAnularAnticipo = document.querySelector(".btn-anular-anticipo");
if(btnAnularAnticipo){
    btnAnularAnticipo.addEventListener("click", async function(e){
        let idAnticipo = document.getElementById("edit-id-anticipo").value;
        //let userId = btnAutorizarAprobador.getAttribute("data-aprobador");

        let dniSolicitante = document.getElementById("edit-dni-solicitante").value;
        let solicitanteNombre = document.getElementById("edit-solicitante").value;
        let sscc = document.getElementById("edit-codigo-sscc").value;
        let nombreProyecto = document.getElementById("edit-nombre-proyecto").value;
        let motivoAnticipo = document.getElementById("edit-motivo-anticipo").value;
        let montoTotal = document.getElementById("edit-monto-total").value;
        
        e.preventDefault();
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea anular este anticipo? Esta acción no podrá ser revertida',
            type: 'confirm',
            event: 'confirm-comment'
        });

        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        
        acceptButton.onclick = async () => {

            const comentario = document.getElementById('custom-alert-comentario').value.trim();

            // Si el boton se encuentra desactivado, no se realizará nada
            if (acceptButton.disabled) {
                return;
            }

            const formData = new FormData();

            acceptButton.disabled = true;
            cancelButton.disabled = true;

            formData.append("id", idAnticipo);
            formData.append("comentario", comentario);
            formData.append("dniSolicitante", dniSolicitante);
            formData.append("solicitanteNombre", solicitanteNombre);
            formData.append("sscc", sscc);
            formData.append("nombreProyecto", nombreProyecto);
            formData.append("motivoAnticipo", motivoAnticipo);
            formData.append("montoTotal", montoTotal);

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';

            try {
                const response = await fetch('anticipos/anularAnticipo', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                if (result.success) {
                    showAlert({
                        title: 'Completado',
                        message: `El anticipo fue anulado correctamente.`,
                        type: 'success',
                        event: 'envio'
                    });
                    editAnticipoModal.style.display = 'none';
                } else {
                    showAlert({
                        title: 'Error',
                        message: `El anticipo no pudo ser marcado como observado. "${result.error}".`,
                        type: 'error',
                        event: 'envio'
                    });
                }
            } catch (error) {
                showAlert({
                    title: 'Error',
                    message: `No se pudo enviar la información del anticipo.`,
                    type: 'error',
                    event: 'envio'
                });
            } finally {
                // Ocultar el modal de carga independientemente del resultado
                loadingModal.style.display = 'none';
                acceptButton.disabled = false;
                cancelButton.disabled = false;
            }
        };

        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };

        // Deshabilitar el botón por defecto
        acceptButton.disabled = true;
        acceptButton.style.opacity = "0.5"; // efecto visual
        acceptButton.style.cursor = "not-allowed";

        // Activar/desactivar según la longitud del comentario
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
    })
}

const btnAbonarAnticipo = document.querySelector(".btn-abonar-anticipo");
if(btnAbonarAnticipo){
    btnAbonarAnticipo.addEventListener("click", async function(e){
        let idAnticipo = document.getElementById("edit-id-anticipo").value;
        //let userId = btnAutorizarAprobador.getAttribute("data-aprobador");

        let dniSolicitante = document.getElementById("edit-dni-solicitante").value;
        let solicitanteNombre = document.getElementById("edit-solicitante").value;
        let sscc = document.getElementById("edit-codigo-sscc").value;
        let nombreProyecto = document.getElementById("edit-nombre-proyecto").value;
        let motivoAnticipo = document.getElementById("edit-motivo-anticipo").value;
        let fechaFin = document.getElementById("edit-fecha-finalizacion").value;
        let montoTotal = document.getElementById("edit-monto-total").value;
        
        //console.log(fechaFin);

        e.preventDefault();
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea marcar este anticipo como abonado?.',
            type: 'confirm',
            event: 'confirm'
        });

        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        
        acceptButton.onclick = async () => {
            const formData = new FormData();

            acceptButton.disabled = true;
            cancelButton.disabled = true;

            formData.append("id", idAnticipo);
            formData.append("dniSolicitante", dniSolicitante);
            formData.append("solicitanteNombre", solicitanteNombre);
            formData.append("sscc", sscc);
            formData.append("nombreProyecto", nombreProyecto);
            formData.append("motivoAnticipo", motivoAnticipo);
            formData.append("fechaFin", fechaFin);
            formData.append("montoTotal", montoTotal);

            // Mostrar el modal de carga
            const loadingModal = document.getElementById('loadingModal');
            loadingModal.style.display = 'flex';
            
            try {
                const response = await fetch('anticipos/abonarAnticipo', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                //console.log(result);

                if (result.success) {
                    showAlert({
                        title: 'Completado',
                        message: `El anticipo fue abonado correctamente.`,
                        type: 'success',
                        event: 'envio'
                    });
                    editAnticipoModal.style.display = 'none';
                } else {
                    showAlert({
                        title: 'Error',
                        message: `El anticipo no pudo ser abonado. "${result.error}".`,
                        type: 'error',
                        event: 'envio'
                    });
                }
            } catch (error) {
                showAlert({
                    title: 'Error',
                    message: `No se pudo enviar la información del anticipo.`,
                    type: 'error',
                    event: 'envio'
                });
            } finally {
                // Ocultar el modal de carga independientemente del resultado
                loadingModal.style.display = 'none';
                acceptButton.disabled = false;
                cancelButton.disabled = false;
            }
        };

        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    })
}

const btnAnticipoViaticosDetalles = document.querySelector(".viaticos-detalles");

if(btnAnticipoViaticosDetalles){
    btnAnticipoViaticosDetalles.addEventListener("click", function() {
        let idAnticipo = document.getElementById("edit-id-anticipo").value;
        if (idAnticipo) {
            const modal = document.getElementById("detalleViaticosModal");
            const modalContent = document.getElementById("detalle-viaticos-content");

            // Mostrar el modal
            modal.style.display = "block";

            // Cargar contenido dinámicamente
            fetch(`detallesViaticos?id_anticipo=${idAnticipo}`)
                .then(response => response.text())
                .then(html => {
                    modalContent.innerHTML = html;
                    // Añadir evento al botón de impresión dentro del modal
                    const printButton = modalContent.querySelector("#print-viaticos");
                    if (printButton) {
                        printButton.addEventListener("click", function() {
                            // const cerrarButton = modalContent.querySelector(".cerrar-detalles-viaticos");
                            // if (cerrarButton) cerrarButton.style.display = "none";
                            document.querySelector(".modal-header-detalles-viaticos").style.display = "none";
                            document.getElementById("open-responsive-menu").style.display = "none";
                            document.getElementById("user-first-info").style.display = "none";
                            printButton.style.display = "none";
                            window.print();
                            // Restaurar visibilidad después de imprimir
                            setTimeout(() => {
                                document.getElementById("open-responsive-menu").style.display = "";
                                document.getElementById("user-first-info").style.display = "";
                                document.querySelector(".modal-header-detalles-viaticos").style.display = "";
                                printButton.style.display = "";
                                // cerrarButton.style.display = "";
                            }, 1000);
                        });
                    }
                })
                .catch(error => {
                    modalContent.innerHTML = "<p>Error al cargar los detalles: " + error.message + "</p>";
                    console.error("Error al cargar detalles:", error);
                });
        }
    });
}

// cerrar el modal al hacer clic en "X"
document.querySelectorAll(".btn-close-modal").forEach(button => {
    button.addEventListener("click", function() {
        const modalId = this.getAttribute("data-modal");
        //console.log(modalId);
        document.getElementById(modalId).style.display = "none";
    });
});