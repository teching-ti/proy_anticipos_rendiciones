document.addEventListener("DOMContentLoaded", function(){

    // Funcionalidad que se ejecuta tras cargar la página de anticipos, verifica si se encuentra algún parámetro en la url
    // si eexiste un parámetros dentro llamado openModal, entonces usará la funcionalidad openAddNewModal
    window.addEventListener('load', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('openModal') === 'true') {
            openModal('addUserModal');
            // Limpia el parámetro de la url para no mostrarlo, es opcional
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    //console.log("Vista de Usuarios")
    // Funciones para abrir y cerrar modales
    function openModal(modalId) {
        document.getElementById(modalId).style.display="block";
        //document.getElementById(modalId).classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display="none";
    }

     // Botón "Agregar Anticipo"
    document.querySelector('.btn-add-usuario').addEventListener('click', () => openModal('addUserModal'));

    // Botones "Cerrar" modal
    document.querySelectorAll('.btn-close-modal').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modal;
            closeModal(modalId);
        });
    });

    // Función para mostrar el cargado de la búsqueda
    // document.querySelector(".lupa").addEventListener("click", function(){
    //     alert("Buscando trabajador... (En proceso de implementación)");
    // });
    
    document.querySelector(".btn-guardar-usuario").addEventListener("click", function(e){
        //e.preventDefault();
        console.log("btn guardar usuario");
    })

    // Autocompletar el login de usuarios
    const searchButton = document.querySelector('.lupa');
    const dniInput = document.getElementById('doc-identidad');
    const userNombreInput = document.getElementById('user-nombre');
    const userContraInput = document.getElementById('user-contra');
    const userRol = document.getElementById('user-rol');
    const nombresInput = document.getElementById('user-nombre-completo');
    const cargoInput = document.getElementById('user-cargo');
    const departamentoInput = document.getElementById('user-departamento');

    let limpiarCasillas = () => {
        dniInput.value='';
        userNombreInput.value = '';
        userContraInput.value = '';
        userRol.value='3';
        nombresInput.value='';
        cargoInput.value='';
        departamentoInput.value='';
    }

    if (searchButton) {
        searchButton.addEventListener('click', () => {
            const dni = dniInput.value.trim();
            console.log('Buscando DNI:', dni);

            // if (!/^[0-9]{8}$/.test(dni)) {
            //     alert('El DNI debe tener 8 dígitos.');
            //     return;
            // }

            fetch('usuarios/searchByDni', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `doc-identidad=${encodeURIComponent(dni)}`
            })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta:', data);
                if (data.success) {
                    nombresInput.value = data.data.nombres+" "+data.data.apellidos;
                    cargoInput.value = data.data.cargo;
                    departamentoInput.value = data.data.departamento_nombre; // nombre del departamento
                    //departamentoInput.value = data.data.departamento; id del departamento
                    userContraInput.value = "********";
                    // lógica para colocar también el nombre de usuario, se extrae del correo presente en la plataforma de HSQE
                    let indiceArroba = data.data.correo.indexOf('@');
                    userNombreInput.value = data.data.correo.substring(0, indiceArroba);
                } else {
                    //alert(data.message || 'Error al buscar el trabajador.');
                    showAlert(
                        {
                            title: 'Error',
                            message: data.message,
                            type: 'error'
                        }
                    );
                    limpiarCasillas();
                }
            })
            .catch(error => {
                console.error('Error en AJAX:', error);
                showAlert({
                    title: 'Error',
                    message: 'No se pudo completar la búsqueda',
                    type: 'error'
                });
                //alert('Error al conectar con el servidor.');
            });
        });
    }

    document.querySelector(".btn-limpiar").addEventListener("click", (e)=>{
        e.preventDefault();
        //alert();
        limpiarCasillas();
    });
});