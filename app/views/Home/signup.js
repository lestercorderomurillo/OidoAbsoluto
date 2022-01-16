$('#signupForm').on("submit", function () {

    if (allComponentsValidated()){

        $('#submit').prop("disabled", true);
        $('#submit').html("Subiendo formulario...");
        
        return true;
    }

    return false;
    
});

$("#signupForm").validate({
    rules: {
        firstName: {
            required: true,
            minlength: 3
        },
        lastName: {
            required: true,
            minlength: 4
        },
        email: {
            required: true,
            email: true
        },
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
        phone: {
            required: true,
            number: true,
            minlength: 7,
            maxlength: 13
        },
        gender: {
            required: true
        },
        country: {
            required: true
        }
    },
    messages: {
        firstName: {
            required: "El campo del nombre no puede estar vacio",
            minlength: "El nombre debe de ser al menos de 3 carácteres"
        },
        lastName: {
            required: "El campo del apellido no puede estar vacio",
            minlength: "El nombre debe de ser al menos de 3 carácteres"
        },
        email: {
            required: "El campo del email no puede estar vacio",
            email: "El formato correcto es: abc@domain.com"
        },
        password: {
            required: "La contraseña no puede estar vacio",
            minlength: "La contraseña debe ser de al menos de 7 carácteres",
            maxlength: "Demasiada extenso"
        },
        confirmPassword: {
            required: "La contraseña no puede estar vacio",
            equalTo: "Ambas contraseñas deben coincidir",
            minlength: "La contraseña debe ser de al menos de 7 carácteres",
            maxlength: "Demasiada extenso"
        },
        phone: {
            required: "El campo de teléfono no puede estar vacio",
            number: "Debe ser un valor numérico",
            minlength: "Demasiado corto",
            maxlength: "Demasiado extenso"
        },
        gender: {
            required: "Debe seleccionar una opción"
        },
        country: {
            required: "Debe seleccionar una opción"
        }
    }
});