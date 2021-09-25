$(document).ready(function() {
    $(".error").hide();

    $(".app-focuseable").keyup(function() {
        if (this.value.length == this.maxLength) {
            $(this).next('.app-focuseable').focus();
        }
    });

    $("body").addClass('body-loaded');
});

class PypeDOM {

    constructor(){
        this.handler = {};
        this.state = ObservableSlim.create(this.handler, true, function (changes) {
            for (var i = 0; i < changes.length; i++) {
                if (changes[i]["type"] == "add" || changes[i]["type"] == "update") {
                    $("#app-sync-" + changes[i]["property"]).html(changes[i]["newValue"]);
                }
            }
        });
    }

    onStart(functionCallback) {
        functionCallback(this.state);
        return this.state;
    }

    onUpdate(functionCallback) {
        setInterval(functionCallback, 16, this.state);
        return this.state;
    }

    static renderNow(functionCallback, delay = 1) {
        setTimeout(function() {
            functionCallback(this.state);
        }, delay);

        return this.state;
    }
}