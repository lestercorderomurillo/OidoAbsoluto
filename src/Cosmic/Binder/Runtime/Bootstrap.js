/**
 * Run this function when the application has been initialized.
 */
$(function () {
    $(".error").hide();
    $(".Focuseable").keyup(function () {
        if (this.value.length == this.maxLength) {
            $(this).next('.Focuseable').focus();
        }
    });
    $("Body").addClass('BodyLoaded');
});