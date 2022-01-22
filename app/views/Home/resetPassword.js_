$("#resetPasswordForm").validate({
    rules: {
        password: {
            required: true,
            minlength: 7,
            maxlength: 24
        },
        confirmPassword: {
            required: true,
            equalTo: "#password",
            minlength: 7,
            maxlength: 24
        },
    },
    messages: {
        password: {
            required: "El campo de contraseña no puede estar vacio",
            minlength: "La contraseña debe ser de al menos de 7 carácteres",
            maxlength: "Demasiada extenso"
        },
        confirmPassword: {
            required: "La contraseña no puede estar vacio",
            equalTo: "Ambas contraseñas deben coincidir",
            minlength: "La contraseña debe ser de al menos de 7 carácteres",
            maxlength: "Demasiada extenso"
        },
    }
});