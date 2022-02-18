$('#signupForm').submit(function (event) {

    let lang = document.documentElement.lang.toLowerCase();
    if (!Validator.validateDate(lang, "birthDay")) {
        event.preventDefault();
        return false;
    }

    if (!$('#signupForm').valid()) {
        event.preventDefault();
        return false;
    }

    Validator.disableDateSubComponents("birthDay");

    $('#submit').prop("disabled", true);
    $('#submit').html("...");

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
    }
});