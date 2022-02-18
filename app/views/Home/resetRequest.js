$("#resetRequestForm").validate({
    rules: {
        email: {
            required: true,
            email: true
        },
    }
});