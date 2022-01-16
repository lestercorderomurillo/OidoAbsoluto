validateForm("loginForm", {
    rules: {
        email: {
            required: true,
            email: true
        },
        password: {
            required: true,
            minlength: 7,
            maxlength: 24
        }
    },
    messages: {
        email: {
            required: "El campo debe contener un correo electrónico",
            email: "El formato correcto es: abc@domain.com"
        },
        password: {
            required: "El campo de contraseña no puede estar vacio",
            minlength: "La contraseña debe ser de al menos de 7 carácteres",
            maxlength: "Demasiada extenso"
        }
    }
});