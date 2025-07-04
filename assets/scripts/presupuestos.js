// Sincronizar saldo_final y saldo_disponible con saldo_inicial
document.getElementById('saldo_inicial').addEventListener('input', function() {
    const saldo = parseFloat(this.value) || 0;
    document.getElementById('saldo_final').value = saldo.toFixed(2);
    document.getElementById('saldo_disponible').value = saldo.toFixed(2);
});

// Cargar CCs al abrir el modal
document.getElementById('btnCrearPresupuesto').addEventListener('click', function() {
    document.getElementById('createPresupuestoModal').style.display = 'flex';
    fetch('presupuestos/get_ccs')
        .then(response => response.json())
        .then(data => {
            const selectCC = document.getElementById('cc_codigo');
            selectCC.innerHTML = '<option value="">Seleccione un CC</option>';
            data.forEach(cc => {
                const option = document.createElement('option');
                option.value = cc.codigo;
                option.textContent = cc.nombre;
                selectCC.appendChild(option);
            });
            selectCC.disabled = false;
        });
});

// Cargar SCCs al cambiar CC
document.getElementById('cc_codigo').addEventListener('change', function() {
    const ccCodigo = this.value;
    if (ccCodigo) {
        fetch(`presupuestos/get_sccs?cc_id=${ccCodigo}`)
            .then(response => response.json())
            .then(data => {
                const selectSCC = document.getElementById('scc_codigo');
                selectSCC.innerHTML = '<option value="">Seleccione un SCC</option>';
                data.forEach(scc => {
                    const option = document.createElement('option');
                    option.value = scc.codigo;
                    option.textContent = scc.nombre;
                    selectSCC.appendChild(option);
                });
                selectSCC.disabled = false;
                document.getElementById('sscc_codigo').disabled = true;
                document.getElementById('btnNextStep').disabled = true;
            });
    } else {
        document.getElementById('scc_codigo').disabled = true;
        document.getElementById('sscc_codigo').disabled = true;
        document.getElementById('btnNextStep').disabled = true;
    }
});

// Cargar SSCCs disponibles al cambiar SCC
document.getElementById('scc_codigo').addEventListener('change', function() {
        const sccCodigo = this.value;
        if (sccCodigo) {
            fetch(`presupuestos/get_ssccs?scc_id=${sccCodigo}`)
                .then(response => response.json())
                .then(data => {
                    const selectSSCC = document.getElementById('sscc_codigo');
                    selectSSCC.innerHTML = '<option value="">Seleccione un SSCC</option>';
                    data.forEach(sscc => {
                        const option = document.createElement('option');
                        option.value = sscc.codigo;
                        option.textContent = sscc.nombre;
                        selectSSCC.appendChild(option);
                    });
                    selectSSCC.disabled = false;
                    document.getElementById('btnNextStep').disabled = false;
                });
        } else {
            document.getElementById('sscc_codigo').disabled = true;
            document.getElementById('btnNextStep').disabled = true;
        }
    });

// Avanzar al siguiente paso
document.getElementById('btnNextStep').addEventListener('click', function() {
    document.getElementById('step-1').style.display = 'none';
    document.getElementById('step-2').style.display = 'block';
    document.getElementById('btnPrevStep').style.display = 'inline-block';
    this.style.display = 'none';
});

// Volver al paso anterior
document.getElementById('btnPrevStep').addEventListener('click', function() {
    document.getElementById('step-2').style.display = 'none';
    document.getElementById('step-1').style.display = 'block';
    document.getElementById('btnNextStep').style.display = 'inline-block';
    this.style.display = 'none';
});

// Enviar formulario con AJAX
document.getElementById('createPresupuestoForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showAlert({
        title: 'Confirmación',
        message: '¿Estás seguro de que deseas registrar este presupuesto? Esta acción no se puede deshacer.',
        type: 'info',
        event: 'confirm'
    });

    const acceptButton = document.getElementById('custom-alert-btn-aceptar');
    const cancelButton = document.getElementById('custom-alert-btn-cancelar');

    acceptButton.onclick = () => {
        const modal = document.getElementById('custom-alert-modal');
        if (modal && modal.style.display !== 'none') {
            const formData = new FormData(this);

            fetch('presupuestos/add', {
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
                if (data.success) {
                    document.getElementById('createPresupuestoModal').style.display = 'none';
                }
            })
            .catch(error => {
                modal.style.display = 'none';
                showAlert({
                    title: 'Error',
                    message: 'Error al procesar la solicitud.',
                    type: 'error'
                });
                console.error('Error:', error);
            });
        }
    };

    cancelButton.onclick = () => {
        const modal = document.getElementById('custom-alert-modal');
        if (modal) modal.style.display = 'none';
    };
});


// Funcionalidad para cerrar los formularios de presupuestos
const btnCerrarModal = document.querySelectorAll(".btn-close-modal");
btnCerrarModal.forEach((e)=>{
    e.addEventListener("click", function(){
        let modal = this.getAttribute("data-modal");
        document.getElementById(modal).style.display = "none";
    })
})

// Manejar clic en botón de añadir fondos
document.querySelectorAll('.btn-add-fondos').forEach(button => {
    button.addEventListener('click', function() {
        const presupuestoId = this.getAttribute('data-id');
        const row = this.closest('tr');
        const saldoDisponible = parseFloat(row.cells[4].textContent); // Columna Saldo Disponible

        document.getElementById('presupuestoId').value = presupuestoId;
        document.getElementById('saldoDisponible').value = saldoDisponible.toFixed(2);
        document.getElementById('addFundsModal').style.display = 'flex';
    });
});

// Enviar formulario de añadir fondos con AJAX
document.getElementById('addFundsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showAlert({
        title: 'Confirmación',
        message: '¿Estás seguro de que deseas añadir estos fondos? Esta acción no se puede deshacer.',
        type: 'info',
        event: 'confirm'
    });

    const acceptButton = document.getElementById('custom-alert-btn-aceptar');
    const cancelButton = document.getElementById('custom-alert-btn-cancelar');

    acceptButton.onclick = () => {
        const modal = document.getElementById('custom-alert-modal');
        if (modal && modal.style.display !== 'none') {
            const formData = new FormData(this);

            fetch('presupuesto_sscc/add_funds', {
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
                if (data.success) {
                    document.getElementById('addFundsModal').style.display = 'none';
                    //location.reload(); // Recargar para actualizar la tabla
                }
            })
            .catch(error => {
                modal.style.display = 'none';
                showAlert({
                    title: 'Error',
                    message: 'Error al procesar la solicitud.',
                    type: 'error'
                });
                console.error('Error:', error);
            });
        }
    };

    cancelButton.onclick = () => {
        const modal = document.getElementById('custom-alert-modal');
        if (modal) modal.style.display = 'none';
    };
});