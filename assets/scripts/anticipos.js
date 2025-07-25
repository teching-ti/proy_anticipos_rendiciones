document.addEventListener('DOMContentLoaded', () => {

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

    // Botón "Agregar Anticipo"
    document.querySelector('.btn-add-anticipo').addEventListener('click', () => openModal('addAnticipoModal'));

    // Botones "Cerrar" modal
    document.querySelectorAll('.btn-close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modal;
            closeModal(modalId);
        });
    });

    // Agregar Nombre del proyecto
    document.getElementById("codigo_sscc").addEventListener("change", function(){
        console.log("Cambiando");
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
});

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
    if (currentStep < steps.length - 1) {
        showStep(currentStep + 1);
    }
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
    personaIndices.push(newIndex);
    personaIndices.sort((a, b) => a - b);

    // Crear botón de pestaña
    const newTabBtn = document.createElement("div");
    newTabBtn.className = "tab-button";
    newTabBtn.dataset.tab = `persona-${newIndex}`;
    newTabBtn.textContent = `Persona ${newIndex}`;
    newTabBtn.id = `tab-persona-${newIndex}`;
    document.getElementById("tabs-header").insertBefore(newTabBtn, document.getElementById("add-tab"));

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
            <div class="transporte-prov-list" id="transp-prov-list-${newIndex}"></div>
            <div class="btn add-transp-provincial" data-persona="${newIndex}">Añadir</div>
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
                <input type="number" class="form-control" name="dias-hospedaje-${newIndex}" readonly>
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
                <input type="number" class="form-control" name="dias-movilidad-${newIndex}" readonly>
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
                <input type="number" class="form-control" name="dias-alimentacion-${newIndex}" readonly>
            </div>
            <div class="modal-element">
                <span class="placeholder">Monto</span>
                <input type="number" class="form-control monto-alimentacion" name="monto-alimentacion-${newIndex}" readonly>
            </div>
        </div>
        <div class='container-remove-persona'>
            <div class="btn remove-persona-btn" data-index="${newIndex}">Eliminar</div>
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

        // se quita el readonly para los dias del dataset
        const inputDiasMovilidad = document.querySelector(`[name='dias-movilidad-${index}']`);
        const inputDiasHospedaje = document.querySelector(`[name='dias-hospedaje-${index}']`);
        const inputDiasAlimentacion = document.querySelector(`[name='dias-alimentacion-${index}']`);
        inputDiasMovilidad.removeAttribute('readonly');
        inputDiasHospedaje.removeAttribute('readonly');
        inputDiasAlimentacion.removeAttribute('readonly');

        // Recalcular montos para los días ya ingresados
        ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
            calcularMonto(index, tipo);
        });
    });

    // Conectar cálculo automático para los inputs de días
    function conectarCalculoAutomatico(index, tipo) {
        const diasInput = document.querySelector(`[name='dias-${tipo}-${index}']`);
        diasInput.addEventListener("input", () => {
            calcularMonto(index, tipo);
        });
    }

    // Implementación de funcionalidad para que se realice el recálculo correspondiente
    ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
        conectarCalculoAutomatico(newIndex, tipo);
    });

    // Escuchar cambios en el nuevo input de monto
    newTabContent.querySelector(".monto-hospedaje").addEventListener("input", actualizarTotalGastos);
    newTabContent.querySelector(".monto-movilidad").addEventListener("input", actualizarTotalGastos);
    newTabContent.querySelector(".monto-alimentacion").addEventListener("input", actualizarTotalGastos);

    // Escuchar los clicks en lupa para autocompletar nombres del trabajador
    newTabContent.querySelector(".lupa").addEventListener("click", async function(){
        const index = this.dataset.index;
        const docId = document.querySelector(`[name='doc-id-${index}']`).value;
        const inputNombres = document.querySelector(`[name=persona-nombre-${index}]`);
        const res = await fetch(`usuarios/anticipoBuscarDni?doc-identidad=${docId}`);
        const data = await res.json();
        
        console.log(data);
        
        if(data.success){
            inputNombres.value = `${data.data.nombres} ${data.data.apellidos}`;
        }else{
            alert("No se encontraron datos del trabajador.");
        }
    });
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

        // Escuchar cambios en el nuevo input de monto
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

    // Evento para eliminar este gasto
    nuevoGasto.querySelector(".remove-gasto-btn").addEventListener("click", () => {
        nuevoGasto.remove();
        actualizarTotalGastos();
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
    if (montoTotal) montoTotal.value = total;

    // Actualizando el elemento visual de "Monto total con el monto calculado"
    montoTotal.value = total;

    // Líneas de código para mensaje de advertencia
    const codigoSscc = document.getElementById("codigo_sscc").value;
    const response = await fetch(`anticipos/getSaldoDisponibleTiempoReal?codigo_sscc=${codigoSscc}`);

    const data = await response.json();
    console.log(`Monto SSCC: ${data}`);
    
    if(montoTotal.value>data){
        //console.log(`No se podrá crear este anticipo el monto total ${montoTotal.value} supera a ${data}`)
        montoTotal.style.outline = "2px solid red";
        showAlert({
            title: 'Advertencia',
            message: `Esta solicitud no puede ser procesada porque supera el limite del presupuesto asignado para el [SSCC]. Por favor, contacte al área de contabilidad.`,
            type: 'error',
            event: 'error'
        });
    }else{
        console.log("Todo en orden");
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

//here here here
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

            fetch('anticipos/add', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                modal.style.display = 'none';

                showAlert({
                    title: data.success ? 'Éxito' : 'Error',
                    message: data.message,
                    type: data.success ? 'success' : 'error',
                    event: data.success ? 'envio' : ''
                });
            })
            .catch(error => {
                modal.style.display = 'none';
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

/*********************************************************************here */
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

    // Sumar montos de transporte // here revisar
    const montosTransporte = document.querySelectorAll(`input[name*='${formPrefix}detalles_viajes'][name*='[transporte]'][name$='[monto]']`);
    montosTransporte.forEach(input => {
        const valor = parseFloat(input.value);
        const container = input.closest('.transp-prov-element');
        const validoInput = container.querySelector(`input[name*='[valido]']`);
        if (!isNaN(valor) && (!validoInput || validoInput.value === '1')) {
            total += valor;
            console.log(`Transporte: ${input.name} = ${valor}`);
        }
    });

    // const montosTransporte = document.querySelectorAll(`input[name*='${formPrefix}detalles_viajes'][name*='[transporte]'][name$='[monto]']`);
    // montosTransporte.forEach(input => {
    //     const valor = parseFloat(input.value);
    //     const container = input.closest('.transp-prov-element');
    //     const validoInput = container.querySelector(`input[name*='[valido]']`);
    //     if (!isNaN(valor) && (!validoInput || validoInput.value === '1')) total += valor;
    // });


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
                montoTotalInput.style.border = '2px solid red';
                console.log(`No se podrá actualizar este anticipo. El monto total ${total.toFixed(2)} supera el saldo disponible ${saldoDisponible.toFixed(2)}.`);
            } else {
                montoTotalInput.style.border = '';
                console.log('Monto total dentro del saldo disponible.');
            }
        } catch (error) {
            console.error('Error al validar monto total:', error);
            montoTotalInput.style.border = '';
        }
    } else {
        montoTotalInput.style.border = '';
    }

    // Depuración
    console.log('Montos gastos menores:', Array.from(montosGastos).map(input => input.value));
    console.log('Montos viáticos:', Array.from(montosViaticos).map(input => input.value));
    console.log('Montos transporte:', Array.from(montosTransporte).map(input => input.value));
    console.log('Total calculado:', total.toFixed(2));
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
    toggleEditMode(colorModeSwitch.checked);

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
        const validoInput = gastoDiv.querySelector(`input[name*='[valido]']`);
        if (validoInput) {
            validoInput.value = '0';
            gastoDiv.style.display = 'none';
        } else {
            gastoDiv.remove();
        }
        actualizarTotalGastosEdit('edit-');
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
            console.log(data);
            await showAnticipoDetails(data);
        } catch (error) {
            console.error('Error al cargar detalles del anticipo:', error);
            alert('No se pudieron cargar los detalles del anticipo.');
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
    editForm.querySelector("#edit-estado-anticipo").value = data.estado || '';
    editForm.querySelector("#edit-monto-total").value = (parseFloat(data.monto_total_solicitado) || 0).toFixed(2);

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
                    <input type="text" class="form-control" name="edit-detalles_gastos[${index}][motivo]" value="${gasto.motivo || ''}">
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
                const validoInput = gastoDiv.querySelector(`input[name*='[valido]']`);
                if (validoInput) {
                    validoInput.value = '0';
                    gastoDiv.style.display = 'none';
                } else {
                    gastoDiv.remove();
                }
                actualizarTotalGastosEdit('edit-');
            });
        });
    } else {
        editForm.querySelector("#edit-viajes").checked = true;
        editForm.querySelector("#edit-compras-menores").checked = false;
        editCambioConcepto();
    }

    // Llenar viáticos y transporte
    if (data.detalles_viajes && data.detalles_viajes.length > 0) {
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
                    <div class="btn edit-adding-transp-provincial" data-persona="${index + 1}">Añadir</div>
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
                        <input type="number" class="form-control" name="edit-dias-hospedaje-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'hospedaje')?.dias || 0}">
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Monto</span>
                        <input type="number" class="form-control monto-hospedaje" name="edit-monto-hospedaje-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'hospedaje')?.monto || 0}">
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
                        <input type="number" class="form-control" name="edit-dias-movilidad-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'movilidad')?.dias || 0}">
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Monto</span>
                        <input type="number" class="form-control monto-movilidad" name="edit-monto-movilidad-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'movilidad')?.monto || 0}">
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
                        <input type="number" class="form-control" name="edit-dias-alimentacion-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'alimentacion')?.dias || 0}">
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Monto</span>
                        <input type="number" class="form-control monto-alimentacion" name="edit-monto-alimentacion-${index + 1}" value="${viaje.viaticos.find(v => v.concepto_nombre.toLowerCase() === 'alimentacion')?.monto || 0}">
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
                    diasInput.removeAttribute('readonly');
                    calcularMontoEdit(idx, tipo);
                });
            });

            ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
                const diasInput = tabContent.querySelector(`[name='edit-dias-${tipo}-${index + 1}']`);
                diasInput.addEventListener("input", () => {
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

            tabContent.querySelectorAll(".edit-remove-transporte-btn").forEach(btn => {
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

            if (index === 0) {
                tabButton.classList.add('active');
                tabContent.style.display = 'block';
            }
        }));
        // Actualizar el total después de cargar todos los datos
        actualizarTotalGastosEdit('edit-');
    }

    colorModeSwitch.checked = false;
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

    if(rolUsuario==2 && (estadoAnticipo=='Nuevo' || estadoAnticipo=='Observado')){
        containerCambioEstado.style.display = 'flex';
    }else if(rolUsuario==4 && estadoAnticipo=='Autorizado'){
        containerCambioEstado.style.display = 'flex';
    }else if(rolUsuario==5 && estadoAnticipo=='Autorizado Totalmente'){
        containerCambioEstado.style.display = 'flex';
    }else{
        containerCambioEstado.style.display = 'none';
    }
}

// Agregar nueva persona en modo edición
editAddTabBtn.addEventListener('click', async function() {
    let newIndex = 1;
    while (editpersonaIndices.includes(newIndex)) {
        newIndex++;
    }
    editpersonaIndices.push(newIndex);
    editpersonaIndices.sort((a, b) => a - b);

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
            <div class="transporte-prov-list" id="edit-transp-prov-list-${newIndex}"></div>
            <div class="btn edit-adding-transp-provincial" data-persona="${newIndex}">Añadir</div>
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
                <input type="number" class="form-control" name="edit-dias-hospedaje-${newIndex}" readonly>
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
                <input type="number" class="form-control" name="edit-dias-movilidad-${newIndex}" readonly>
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
                <input type="number" class="form-control" name="edit-dias-alimentacion-${newIndex}" readonly>
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
            diasInput.removeAttribute('readonly');
            calcularMontoEdit(idx, tipo);
        });
    });

    ['alimentacion', 'hospedaje', 'movilidad'].forEach(tipo => {
        const diasInput = newTabContent.querySelector(`[name='edit-dias-${tipo}-${newIndex}']`);
        diasInput.addEventListener("input", () => {
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
});

// Manejando la eliminación de personas - Edit Panel
editTabsBody.addEventListener("click", function(e) {
    if (e.target.classList.contains("remove-persona-btn")) {
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
    }
});

// Manejar adición de transporte provincial
editTabsBody.addEventListener("click", function(e) {
    if (e.target.classList.contains("edit-adding-transp-provincial")) {
        const persona = e.target.dataset.persona;
        const container = document.getElementById(`edit-transp-prov-list-${persona}`);
        const index = container.children.length;

        const grupo = document.createElement("div");
        grupo.classList.add("transp-prov-element");
        grupo.innerHTML = `
            <input type="hidden" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][valido]" value="1">
            <div class="edit-remove-transporte-btn"><i class="fa-regular fa-trash-can"></i></div>
            <div class="med-transporte">
                <div>
                    <input type="radio" name="edit-tipo-transporte-${persona}-${index}" id="edit-terrestre-${persona}-${index}" value="terrestre" checked>
                    <label for="edit-terrestre-${persona}-${index}">Terrestre</label>
                </div>
                <div>
                    <input type="radio" name="edit-tipo-transporte-${persona}-${index}" id="edit-aereo-${persona}-${index}" value="aereo">
                    <label for="edit-aereo-${persona}-${index}">Aéreo</label>
                </div>
            </div>
            <div class="modal-element">
                <span class="placeholder">Ciudad Origen</span>
                <input type="text" class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][ciudad_origen]">
            </div>
            <div class="modal-element">
                <span class="placeholder">Ciudad Destino</span>
                <input type="text" class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][ciudad_destino]">
            </div>
            <div class="modal-element">
                <span class="placeholder">Fecha</span>
                <input type="date" class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][fecha]" required>
            </div>
            <div class="modal-element">
                <span class="placeholder">Gasto</span>
                <input type="number" class="form-control gasto-viaje" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][monto]" required>
            </div>
            <div class="modal-element">
                <span class="placeholder">Moneda</span>
                <select class="form-control" name="edit-detalles_viajes[${persona - 1}][transporte][${index}][moneda]">
                    <option value="PEN" selected>PEN</option>
                </select>
            </div>
        `;
        container.appendChild(grupo);

        grupo.querySelector(".gasto-viaje").addEventListener("input", () => actualizarTotalGastosEdit('edit-'));
        grupo.querySelector(".edit-remove-transporte-btn").addEventListener("click", () => {
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

// Función para alternar entre modo Ver y Editar
function toggleEditMode(isEditMode) {
    const isEditable = ['Nuevo', 'Observado'].includes(editForm.querySelector("#edit-estado-anticipo").value);
    const inputs = editForm.querySelectorAll('input:not([type="radio"]):not([type="checkbox"]), select');

    inputs.forEach(input => {
        if (['edit-solicitante', 'edit-dni-solicitante', 'edit-departamento', 'edit-cargo', 'edit-fecha-solicitud'].includes(input.id)) {
            input.readOnly = true;
            input.disabled = true;
        } else if (input.name.includes('edit-detalles_gastos') || input.name.includes('edit-detalles_viajes')) {
            // Habilitar todos los campos de detalles_gastos y detalles_viajes en modo edición
            input.readOnly = !isEditMode;
            input.disabled = !isEditMode;
        } else {
            input.readOnly = !isEditMode;
            input.disabled = !isEditMode;
        }
    });

    // Selección de elementos que se encontrarán desactivados o deshabilitados mientras no se coloque el anticipo en el modo de editar
    editSubmitButton.disabled = !isEditMode || !isEditable;//here here here
    editSubmitButton.style.display = isEditMode && isEditable ? 'block' : 'none';
    editAddGastoBtn.style.display = isEditMode && isEditable ? 'block' : 'none';
    editAddTabBtn.style.display = isEditMode && isEditable ? 'block' : 'none';

    document.querySelectorAll(".edit-remove-gasto-btn").forEach(e => {
        e.style.display = isEditMode && isEditable ? 'block' : 'none';
    })
    document.querySelectorAll(".edit-remove-transporte-btn").forEach(e=>{
        e.style.display = isEditMode && isEditable ? 'block' : 'none';
    })
    document.querySelectorAll(".container-remove-persona").forEach(e=>{
        e.style.display = isEditMode && isEditable ? 'flex' : 'none';
    })

    document.querySelectorAll(".edit-adding-transp-provincial").forEach(e=>{
        e.style.display = isEditMode && isEditable ? 'block' : 'none';
    })
    //document.querySelector(".edit-adding-transp-provincial").style.display = isEditMode && isEditable ? 'block' : 'none';
}

// Manejar el interruptor de modo
colorModeSwitch.addEventListener('change', function() {
    toggleEditMode(this.checked);
});

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
    const formData = new FormData(this);
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
            editAnticipoModal.style.display = 'none';
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
    }
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
        let idAnticipo = document.getElementById("edit-id-anticipo").value;
        //let userId = btnAutorizarAprobador.getAttribute("data-aprobador");
        
        e.preventDefault();
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que deseas autorizar este anticipo? Esta acción no se puede deshacer.',
            type: 'confirm',
            event: 'confirm-comment'
        });
        
        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        
        acceptButton.onclick = async () => {

            const comentario = document.getElementById('custom-alert-comentario').value;
            const formData = new FormData();

            formData.append("id", idAnticipo);
            formData.append("comentario", comentario);

            try {
                const response = await fetch('anticipos/autorizar', {
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
                    editAnticipoModal.style.display = 'none';
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
            }
        };
        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    })
}

// Autorizacion 2 de un anticipo
const btnAutorizarTotalmente = document.querySelector(".btn-aprobar-totalmente");
if(btnAutorizarTotalmente){
    btnAutorizarTotalmente.addEventListener("click", async function(e){
        let idAnticipo = document.getElementById("edit-id-anticipo").value;
        //let userId = btnAutorizarAprobador.getAttribute("data-aprobador");
        
        e.preventDefault();
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea autorizar totalmente este anticipo? Esta acción no se puede deshacer.',
            type: 'confirm',
            event: 'confirm-comment'
        });
        
        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');

        acceptButton.onclick = async () => {

            const comentario = document.getElementById('custom-alert-comentario').value;
            const formData = new FormData();

            formData.append("id", idAnticipo);
            formData.append("comentario", comentario);

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
        
        e.preventDefault();
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea marcar este anticipo como observado? Esta acción no se puede deshacer.',
            type: 'confirm',
            event: 'confirm-comment'
        });

        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        
        acceptButton.onclick = async () => {

            const comentario = document.getElementById('custom-alert-comentario').value;
            const formData = new FormData();

            formData.append("id", idAnticipo);
            formData.append("comentario", comentario);

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
            }
        };
        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    })
}

const btnAbonarAnticipo = document.querySelector(".btn-abonar-anticipo");
if(btnAbonarAnticipo){
    btnAbonarAnticipo.addEventListener("click", async function(e){
        let idAnticipo = document.getElementById("edit-id-anticipo").value;
        //let userId = btnAutorizarAprobador.getAttribute("data-aprobador");
        
        e.preventDefault();
        showAlert({
            title: 'Confirmación',
            message: '¿Estás seguro de que desea marcar este anticipo como observado? Esta acción no se puede deshacer.',
            type: 'confirm',
            event: 'confirm-comment'
        });

        const acceptButton = document.getElementById('custom-alert-btn-aceptar');
        const cancelButton = document.getElementById('custom-alert-btn-cancelar');
        
        acceptButton.onclick = async () => {
            const comentario = document.getElementById('custom-alert-comentario').value;
            const formData = new FormData();

            formData.append("id", idAnticipo);
            formData.append("comentario", comentario);

            try {
                const response = await fetch('anticipos/abonarAnticipo', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
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
            }
        };
        cancelButton.onclick = () => {
            const modal = document.getElementById('custom-alert-modal');
            modal.style.display = 'none';
        };
    })
}