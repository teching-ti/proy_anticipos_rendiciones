document.querySelector(".form-login").addEventListener("submit", async function (e) {
    e.preventDefault(); // Evita recarga del formulario

    const userInput = document.getElementById("form-id").value.trim();
    const passInput = document.getElementById("form-pass").value.trim();


    try {
        const response = await fetch("../scripts/datos.json?v=2");
        const users = await response.json();

        const foundUser = users.find(user =>
            (user.usuario === userInput || user.email === userInput) &&
            user.password === passInput
        );

        if (foundUser) {
            alert(`¡Hola ${foundUser.nombre}! Inicio de sesión exitoso.`);

            sessionStorage.setItem("nombreUsuario", foundUser.nombre);
            sessionStorage.setItem("rolUsuario", foundUser.rol);

            window.location.href = "dashboard.php";
        } else {
            alert("Usuario o contraseña incorrectos.");
        }
    } catch (error) {
        console.error("Error al cargar los datos:", error);
        alert("Ocurrió un error al iniciar sesión.");
    }
});
