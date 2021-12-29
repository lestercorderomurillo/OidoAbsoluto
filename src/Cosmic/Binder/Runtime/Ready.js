$(document).ready(function () {
    $(".Error").hide();
    $(".Focuseable").keyup(function () {
        if (this.value.length == this.maxLength) {
            $(this).next('.Focuseable').focus();
        }
    });
    $("Body").addClass('BodyLoaded');
});