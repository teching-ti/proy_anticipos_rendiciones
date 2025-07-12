document.addEventListener("DOMContentLoaded", function(){
    
})

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
            alert("No se pudieron cargar los detalles de la rendicion");
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

// Inician funcionalidades para cambiar de pestañas dentro del modal de rendiciones
let currentStep = 0;
const steps = [];

function showStep(index) {
    steps.forEach((step, i) => {
        step.classList.toggle("active", i === index);
    });
    currentStep = index;
}

function nextStep() {
    // if (currentStep < steps.length - 1) {
    //     showStep(currentStep + 1);
    // }
    if (currentStep < steps.length - 1) {
            if (currentStep === 0) {
                // Cargar detalles antes de pasar a step-2
                const idAnticipo = document.getElementById("id-anticipo").value;
                if (idAnticipo) {
                    fetch(`rendiciones/getDetallesComprasMenores?id_anticipo=${encodeURIComponent(idAnticipo)}`)
                        .then(response => {
                            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                            return response.json();
                        })
                        .then(detalles => {
                            console.log(detalles);
                            const detallesContainer = document.getElementById("detalles-compras-container");
                            detallesContainer.innerHTML = ''; // Limpiar contenido previo
                            if (detalles.length > 0) {
                                detalles.forEach(item => {
                                    const container = document.createElement('div');
                                    container.className = 'container-detalle';
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
                                                <input type="text" class="rendicion-element" value="0.00">
                                            </div>
                                        </div>
                                        <div class="compras-elementos-dos">
                                            <div class="modal-element">
                                                <span class="placeholder">Fecha</span>
                                                <input type="date" class="rendicion-element" value="${new Date().toISOString().split('T')[0]}">
                                            </div>
                                            <div class="modal-element">
                                                <div class="btn btn-adjuntar">Adjuntar</div>
                                            </div>
                                            <div class="enlace-factura">
                                                <a href="#">F202-00001330</a>
                                            </div>
                                        </div>
                                    `;
                                    detallesContainer.appendChild(container);
                                });
                            } else {
                                detallesContainer.innerHTML = '<p>No hay detalles de compras menores válidos.</p>';
                            }
                        })
                        .catch(error => {
                            console.error('Error al cargar detalles de compras menores: ', error);
                            alert("No se pudieron cargar los detalles de compras menores");
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

showStep(currentStep);

// exportación global
window.nextStep = nextStep;
window.prevStep = prevStep;

document.querySelectorAll(".form-step").forEach(step => steps.push(step));
// Terminan funcionalidades para cambiar de pestañas dentro del modal de rendiciones

document.getElementById("btn-guardar-rendicion").addEventListener("click", function(e){
    e.preventDefault();
});