$("#resetRequestForm").validate({
    rules: {
        email: {
            required: true,
            email: true
        },
    },
    messages: {
        email: {
            required: "El campo debe contener un correo electrónico",
            email: "El formato correcto es: abc@domain.com"
        }
    }
});