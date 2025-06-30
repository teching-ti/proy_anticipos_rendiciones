document.addEventListener('DOMContentLoaded', () => {
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
    
    // Validar formulario de agregar anticipo
    function validateAddForm(form) {
        const fields = ['solicitante', 'area', 'cargo', 'nombre_proyecto', 'motivo_anticipo'];
        for (const field of fields) {
            const value = form.querySelector(`[name="${field}"]`).value.trim();
            if (!validateInput(value)) {
                showAlert({
                    title: 'Error',
                    message: `El campo ${field.replace('_', ' ')} solo puede contener letras, números y espacios.`,
                    type: 'error'
                });
                return false;
            }
        }
        const dni = form.querySelector('[name="dni_solicitante"]').value.trim();
        if (!/^[0-9]{8}$/.test(dni)) {
            showAlert({
                title: 'Error',
                message: 'El DNI del solicitante debe tener 8 dígitos.',
                type: 'error'
            });
            return false;
        }
        const monto = form.querySelector('[name="monto_total_solicitado"]').value;
        if (monto <= 0) {
            showAlert({
                title: 'Error',
                message: 'El monto debe ser mayor a 0.',
                type: 'error'
            });
            return false;
        }
        const fecha = form.querySelector('[name="fecha_solicitud"]').value;
        if (!/^\d{4}-\d{2}-\d{2}$/.test(fecha)) {
            showAlert({
                title: 'Error',
                message: 'La fecha de solicitud debe tener el formato YYYY-MM-DD.',
                type: 'error'
            });
            return false;
        }
        return true;
    }

    // Validar formularios de aprobar/rechazar
    function validateActionForm(form) {
        const comentario = form.querySelector('[name="comentario"]').value.trim();
        if (comentario && !validateInput(comentario)) {
            showAlert({
                title: 'Error',
                message: 'El comentario solo puede contener letras, números y espacios.',
                type: 'error'
            });
            return false;
        }
        return true;
    }

    // Validar formulario de agregar anticipo
    const addForm = document.querySelector('#addAnticipoModal form');
    if (addForm) {
        addForm.addEventListener('submit', (e) => {
            if (!validateAddForm(addForm)) {
                e.preventDefault();
            }
        });
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
                alert("El monto no puede ser superior a 400 soles para este tipo de gasto.");
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
                alert("El monto no puede ser superior a 400 soles para este tipo de gasto.");
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
            title: 'Error',
            message: `Advertencia. Esta solicitud de anticipo no podrá culminarse, por favor, presente estas observaciones a personal del área de contabilidad.`,
            type: 'error'
        });
        //alert("Advertencia. Esta solicitud de anticipo no podrá culminarse, por favor, presente estas observaciones a personal del área de contabilidad.");//here
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
            console.log(ssccs);
            
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

// funcionalidad para mostrar datos de la solicitud de anticipo
document.querySelectorAll("tr").forEach((e)=>{
    e.addEventListener("dblclick", async function(){
        const anticipoId = e.children[0].textContent;
        
        const res = await fetch(`anticipos/getAnticipoDetails?id_anticipo=${anticipoId}`);
        const data = await res.json();

        //console.log(data);
        //console.log(data.solicitante_nombres);
        showAnticipoDetails(data);
    })
});


// Esta función tiene como únicaa finalidad cargar los datos principales del anticipo (por ahora)
function showAnticipoDetails(data){
    console.log(data);
    const editForm = document.getElementById("editAnticipoModal");
    // Elementos principales del anticipo
    const editAnticipoTitle = document.getElementById("edit-modal-title");
    const editIdAnticipo = document.getElementById("edit-id-anticipo");
    const editSolicitante = document.getElementById("edit-solicitante");
    const editDniSolicitante = document.getElementById("edit-dni-solicitante");
    const editDepartement = document.getElementById("edit-departamento");
    const editCargo = document.getElementById("edit-cargo");
    const editScc = document.getElementById("edit-codigo-scc");
    const editSscc = document.getElementById("edit-codigo-sscc");
    const editNombreProyecto = document.getElementById("edit-nombre-proyecto");
    const editMotivoAnticipo = document.getElementById("edit-motivo-anticipo");
    const editFechaSolicitud = document.getElementById("edit-fecha-solicitud");
    const editMontoTotal = document.getElementById("edit-monto-total");

    editForm.style.display = "block";
    editAnticipoTitle.innerText = `Anticipo #${data.id}`;
    editIdAnticipo.value = data.id;
    editSolicitante.value = data.solicitante_nombres;
    editDniSolicitante.value = data.dni_solicitante;
    editDepartement.value = data.departamento_nombre;
    editCargo.value = data.cargo;
    editScc.value = data.scc_codigo;
    editNombreProyecto.value = data.nombre_proyecto;
    editMotivoAnticipo.value = data.motivo_anticipo;
    editFechaSolicitud.value = data.fecha_solicitud;
    editMontoTotal.value = data.monto_total_solicitado;
}

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