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
    }
});