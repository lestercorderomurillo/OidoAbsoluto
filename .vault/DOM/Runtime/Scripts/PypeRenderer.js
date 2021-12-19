$(document).ready(function() {
    $(".error").hide();

    $(".app-focuseable").keyup(function() {
        if (this.value.length == this.maxLength) {
            $(this).next('.app-focuseable').focus();
        }
    });

    $("body").addClass('body-loaded');
});

class PypeRenderer {

    constructor(){
        this.handler = {};
        this.once = false;
        this.state = ObservableSlim.create(this.handler, true, function (changes) {
            for (var i = 0; i < changes.length; i++) {
                if (changes[i]["type"] == "add" || changes[i]["type"] == "update") {
                    var name = "app-sync-" + changes[i]["property"];
                    $("div[class*='" + name + "']").html(changes[i]["newValue"]);
                }
            }
        });
    }

    onStart(functionCallback) {
        if(!this.once){
            this.once = true;
            functionCallback(this.state);
        }
        return this.state;
    }

    onUpdate(functionCallback) {
        setInterval(functionCallback, 16, this.state);
        return this.state;
    }

    render(functionCallback = null, delayTime = 1) {
        setTimeout(functionCallback, delayTime, this.state);
    }
}