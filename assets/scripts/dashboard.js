let userEstadoAnticipos = null;

document.addEventListener("DOMContentLoaded", async function(){
    const userAnticipo = await fetch(`anticipos/getAnticipoPendiente`);
    userEstadoAnticipos = await userAnticipo.json();
})

function redirectToAnticipo() {
    if(!userEstadoAnticipos){
        window.location.href = 'anticipos?openModal=true';
    }else{
        alert('Estimado usuario. Usted aún tiene un anticipo en proceso, no podrá generar otra solicitud hasta que su anticipo actual se encuentre en estado "Rendido".')
    }
}

function redirectToNewUser() {
    window.location.href = 'usuarios?openModal=true';
}