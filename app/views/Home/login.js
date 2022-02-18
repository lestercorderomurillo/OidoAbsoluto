$("#loginForm").validate({
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
    }
});