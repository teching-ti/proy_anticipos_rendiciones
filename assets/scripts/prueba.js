document.addEventListener("DOMContentLoaded", function() {
    // ... (código existente: actualizarTotalGastos, cargar SSCC, etc.)

    const editAnticipoModal = document.getElementById("editAnticipoModal");
    const editModalTitle = document.getElementById("edit-modal-title");
    const editForm = editAnticipoModal.querySelector("form");
    const editSubmitButton = editForm.querySelector("button[type='submit']");
    const colorModeSwitch = editForm.querySelector("#color_mode");
    const editComprasMenoresPanel = document.getElementById("edit-panel-compras-menores");
    const editViajesPanel = document.getElementById("edit-panel-viajes");
    const editTabsBody = document.getElementById("edit-tabs-body");
    const editTabsHeader = document.getElementById("edit-tabs-header");
    const editAddGastoBtn = editComprasMenoresPanel.querySelector("#add-gasto-btn");
    const editAddTabBtn = editTabsHeader.querySelector("#add-tab");

    // Función para calcular y validar el monto total
    async function actualizarTotalGastos(formPrefix = '') {
        let total = 0;

        // Sumar montos de gastos menores
        const montosGastos = document.querySelectorAll(`input[name*='${formPrefix}detalles_gastos'][name$='[importe]']`);
        montosGastos.forEach(input => {
            const valor = parseFloat(input.value);
            if (!isNaN(valor)) total += valor;
        });

        // Sumar montos de viáticos y transporte
        const montosViajes = document.querySelectorAll(`
            input[name^='${formPrefix}monto-hospedaje-'],
            input[name^='${formPrefix}monto-movilidad-'],
            input[name^='${formPrefix}monto-alimentacion-'],
            input[name^='${formPrefix}gasto-viaje-']
        `);
        montosViajes.forEach(input => {
            const valor = parseFloat(input.value);
            if (!isNaN(valor)) total += valor;
        });

        // Actualizar el input monto-total
        const montoTotalInput = document.querySelector(`#${formPrefix}monto-total`);
        if (montoTotalInput) {
            montoTotalInput.value = total.toFixed(2);
        }

        // Validar monto total contra el saldo disponible
        const ssccSelect = document.querySelector(`#${formPrefix}codigo_sscc`);
        const codigoSscc = ssccSelect.value;
        if (codigoSscc && total > 0) {
            try {
                const response = await fetch(`anticipos/getSaldoDisponibleTiempoReal?codigo_sscc=${encodeURIComponent(codigoSscc)}`);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                if (data.error) throw new Error(data.error);
                const saldoDisponible = parseFloat(data.saldo_disponible) || 0;

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
                    await actualizarTotalGastos(formPrefix);
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
        await actualizarTotalGastos('edit-');
    });

    // Validar monto total al cambiar inputs de gastos
    document.addEventListener('input', async function(event) {
        if (
            event.target.matches("input[name*='edit-detalles_gastos'][name$='[importe]']") ||
            event.target.matches("input[name^='edit-monto-hospedaje-']") ||
            event.target.matches("input[name^='edit-monto-movilidad-']") ||
            event.target.matches("input[name^='edit-monto-alimentacion-']") ||
            event.target.matches("input[name^='edit-gasto-viaje-']")
        ) {
            await actualizarTotalGastos('edit-');
        }
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
                showAnticipoDetails(data);
            } catch (error) {
                console.error('Error al cargar detalles del anticipo:', error);
                alert('No se pudieron cargar los detalles del anticipo.');
            }
        });
    });

    // Función para mostrar los detalles del anticipo en el modal de edición
    function showAnticipoDetails(data) {
        console.log(data);
        editModalTitle.innerText = `Anticipo #${data.id}`;
        editForm.querySelector("#edit-id-anticipo").value = data.id || '';
        editForm.querySelector("#edit-solicitante").value = data.solicitante_nombres || '';
        editForm.querySelector("#edit-dni-solicitante").value = data.dni_solicitante || '';
        editForm.querySelector("#edit-departamento").value = data.departamento_nombre || '';
        editForm.querySelector("#edit-cargo").value = data.cargo || '';
        editForm.querySelector("#edit-codigo-scc").value = data.codigo_scc || '';
        editForm.querySelector("#edit-nombre-proyecto").value = data.nombre_proyecto || '';
        editForm.querySelector("#edit-motivo-anticipo").value = data.motivo_anticipo || '';
        editForm.querySelector("#edit-fecha-solicitud").value = data.fecha_solicitud || '';
        editForm.querySelector("#edit-monto-total").value = (parseFloat(data.monto_total_solicitado) || 0).toFixed(2);

        // Llenar sub-subcentro de costo
        const editSsccSelect = editForm.querySelector("#edit-codigo-sscc");
        editSsccSelect.innerHTML = `<option value="${data.codigo_sscc || ''}">${data.codigo_sscc || ''}</option>`;

        // Limpiar paneles dinámicos
        editComprasMenoresPanel.querySelectorAll('.gasto-menor').forEach(el => el.remove());
        editTabsBody.innerHTML = '';
        editTabsHeader.querySelectorAll('.tab-button:not(.add-tab)').forEach(el => el.remove());

        // Llenar compras menores
        if (data.detalles_gastos && data.detalles_gastos.length > 0) {
            editForm.querySelector("#edit-compras-menores").checked = true;
            editForm.querySelector("#edit-viajes").checked = false;
            editCambioConcepto();
            data.detalles_gastos.forEach((gasto, index) => {
                const gastoDiv = document.createElement('div');
                gastoDiv.className = 'gasto-menor';
                gastoDiv.innerHTML = `
                    <div class="modal-element">
                        <span class="placeholder">Descripción</span>
                        <input type="text" class="form-control" name="edit-detalles_gastos[${index}][descripcion]" value="${gasto.descripcion || ''}" readonly>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Motivo</span>
                        <input type="text" class="form-control" name="edit-detalles_gastos[${index}][motivo]" value="${gasto.motivo || ''}" readonly>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Moneda</span>
                        <input type="text" class="form-control" name="edit-detalles_gastos[${index}][moneda]" value="${gasto.moneda || 'PEN'}" readonly>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Importe</span>
                        <input type="number" class="form-control" name="edit-detalles_gastos[${index}][importe]" value="${gasto.importe || 0}" readonly>
                    </div>
                `;
                editComprasMenoresPanel.insertBefore(gastoDiv, editAddGastoBtn);
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
            data.detalles_viajes.forEach((viaje, index) => {
                // Crear pestaña
                const tabId = `edit-tab-${index + 1}`;
                const tabButton = document.createElement('div');
                tabButton.className = 'tab-button';
                tabButton.dataset.tab = tabId;
                tabButton.textContent = `Persona ${index + 1}`;
                editTabsHeader.insertBefore(tabButton, editAddTabBtn);

                // Crear contenido de la pestaña
                const tabContent = document.createElement('div');
                tabContent.className = 'tab-content';
                tabContent.id = tabId;
                tabContent.innerHTML = `
                    <div class="modal-element">
                        <span class="placeholder">Documento de Identidad</span>
                        <input type="text" class="form-control" name="edit-detalles_viajes[${index}][doc_identidad]" value="${viaje.doc_identidad || ''}" readonly>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Nombre</span>
                        <input type="text" class="form-control" name="edit-detalles_viajes[${index}][nombre_persona]" value="${viaje.nombre_persona || ''}" readonly>
                    </div>
                    <div class="modal-element">
                        <span class="placeholder">Cargo</span>
                        <input type="text" class="form-control" name="edit-detalles_viajes[${index}][cargo_nombre]" value="${viaje.cargo_nombre || viaje.id_cargo || ''}" readonly>
                        <input type="hidden" name="edit-detalles_viajes[${index}][id_cargo]" value="${viaje.id_cargo || ''}">
                    </div>
                    <h4>Viáticos</h4>
                    <table class="table viaticos-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Días</th>
                                <th>Monto</th>
                                <th>Moneda</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${viaje.viaticos.map((viatico, vIndex) => `
                                <tr>
                                    <td><input type="text" class="form-control" name="edit-detalles_viajes[${index}][viaticos][${vIndex}][concepto_nombre]" value="${viatico.concepto_nombre || viatico.id_concepto || ''}" readonly></td>
                                    <td><input type="number" class="form-control" name="edit-detalles_viajes[${index}][viaticos][${vIndex}][dias]" value="${viatico.dias || 0}" readonly></td>
                                    <td><input type="number" class="form-control" name="edit-detalles_viajes[${index}][viaticos][${vIndex}][monto]" value="${viatico.monto || 0}" readonly></td>
                                    <td><input type="text" class="form-control" name="edit-detalles_viajes[${index}][viaticos][${vIndex}][moneda]" value="${viatico.moneda || 'PEN'}" readonly></td>
                                    <input type="hidden" name="edit-detalles_viajes[${index}][viaticos][${vIndex}][id_concepto]" value="${viatico.id_concepto || ''}">
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    <h4>Transporte</h4>
                    <div class="transporte-container">
                        ${viaje.transporte.map((transporte, tIndex) => `
                            <div class="transporte-item">
                                <div class="modal-element">
                                    <span class="placeholder">Tipo de Transporte</span>
                                    <input type="text" class="form-control" name="edit-detalles_viajes[${index}][transporte][${tIndex}][tipo_transporte]" value="${transporte.tipo_transporte || ''}" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Ciudad Origen</span>
                                    <input type="text" class="form-control" name="edit-detalles_viajes[${index}][transporte][${tIndex}][ciudad_origen]" value="${transporte.ciudad_origen || ''}" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Ciudad Destino</span>
                                    <input type="text" class="form-control" name="edit-detalles_viajes[${index}][transporte][${tIndex}][ciudad_destino]" value="${transporte.ciudad_destino || ''}" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Fecha</span>
                                    <input type="date" class="form-control" name="edit-detalles_viajes[${index}][transporte][${tIndex}][fecha]" value="${transporte.fecha || ''}" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Gasto</span>
                                    <input type="number" class="form-control" name="edit-detalles_viajes[${index}][transporte][${tIndex}][monto]" value="${transporte.monto || 0}" readonly>
                                </div>
                                <div class="modal-element">
                                    <span class="placeholder">Moneda</span>
                                    <input type="text" class="form-control" name="edit-detalles_viajes[${index}][transporte][${tIndex}][moneda]" value="${transporte.moneda || 'PEN'}" readonly>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
                editTabsBody.appendChild(tabContent);

                // Activar la primera pestaña
                if (index === 0) {
                    tabButton.classList.add('active');
                    tabContent.style.display = 'block';
                }
            });

            // Manejar cambio de pestañas
            editTabsHeader.querySelectorAll('.tab-button:not(.add-tab)').forEach(button => {
                button.addEventListener('click', function() {
                    editTabsHeader.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                    editTabsBody.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
                    this.classList.add('active');
                    document.getElementById(this.dataset.tab).style.display = 'block';
                });
            });
        }

        // Establecer modo inicial (Ver)
        colorModeSwitch.checked = false;
        editSubmitButton.disabled = true;
        editAddGastoBtn.style.display = 'none';
        editAddTabBtn.style.display = 'none';
        toggleEditMode(false);

        // Mostrar el modal
        editAnticipoModal.style.display = "block";

        // Actualizar monto total
        actualizarTotalGastos('edit-');
    }

    // Función para alternar entre modo Ver y Editar
    function toggleEditMode(isEditMode) {
        const inputs = editForm.querySelectorAll('input:not([type="radio"]):not([type="checkbox"]), select');
        const isEditable = ['Nuevo', 'Pendiente'].includes(editForm.querySelector('td[data-label="Estado"]')?.textContent);
        
        inputs.forEach(input => {
            if (['edit-solicitante', 'edit-dni-solicitante', 'edit-departamento', 'edit-cargo', 'edit-fecha-solicitud'].includes(input.id)) {
                input.readOnly = true;
                input.disabled = true;
            } else {
                input.readOnly = !isEditMode;
                input.disabled = !isEditMode;
            }
        });

        editSubmitButton.disabled = !isEditMode || !isEditable;
        editAddGastoBtn.style.display = isEditMode && isEditable ? 'block' : 'none';
        editAddTabBtn.style.display = isEditMode && isEditable ? 'block' : 'none';
    }

    // Manejar el interruptor de modo
    colorModeSwitch.addEventListener('change', function() {
        toggleEditMode(this.checked);
    });

    // Selección y cambio de vista en sección de Concepto - compras menores o viajes
    const editOpcionesConcepto = document.querySelectorAll("input[name='edit-concepto']");
    function editCambioConcepto() {
        if (document.getElementById("edit-compras-menores").checked) {
            editPanelComprasMenores.style.display = "block";
            editPanelViajes.style.display = "none";
        } else if (document.getElementById("edit-viajes").checked) {
            editPanelComprasMenores.style.display = "none";
            editPanelViajes.style.display = "block";
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
    });

    // ... (resto del código: addGasto, addTab, etc.)
});