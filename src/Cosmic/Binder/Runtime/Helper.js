$(document).ready(function () {
    $(".error").hide();
    $(".Focuseable").keyup(function () {
        if (this.value.length == this.maxLength) {
            $(this).next('.Focuseable').focus();
        }
    });
    $("Body").addClass('BodyLoaded');
});

/**
 * Run a function with the specified delay.
 * 
 * @param {callback} fn The callback to bind.
 * @param {int} time The time to wait before starting the function.
 */
function delay(fn, time) {
    setTimeout(function () {
        fn();
    }, time);
}
