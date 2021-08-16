$(document).ready(function () {
    $(".error").hide();

    $(".v-focuseable").keyup(function () {
        if (this.value.length == this.maxLength) {
            $(this).next('.v-focuseable').focus();
        }
    });

    $("body").addClass('body-loaded');
});

function shuffleArray(array){
    return array.sort(() => Math.random() - 0.5);
}

function createTemplateSynchronizer(func) {
    var handler = {};
    var jsdom = ObservableSlim.create(handler, true, function (changes) {
        for (var i = 0; i < changes.length; i++) {
            if (changes[i]["type"] == "add" || changes[i]["type"] == "update") {
                $(".sync-" + changes[i]["property"]).html(changes[i]["newValue"]);
            }
        }
    });

    func(jsdom);
    setInterval(func, 15, jsdom);

    return jsdom;
}

function toHHMMSS(value) {
    var sec_num = parseInt(value, 10);
    var hours = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours < 10) { hours = "0" + hours; }
    if (minutes < 10) { minutes = "0" + minutes; }
    if (seconds < 10) { seconds = "0" + seconds; }

    return hours + ':' + minutes + ':' + seconds;
}

function isLeapYear(year) {
    return ((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0);
}

function disableDateForSubmit(id) {
    $(`#${id}_yy`).attr("disabled", "disabled");
    $(`#${id}_mm`).attr("disabled", "disabled");
    $(`#${id}_dd`).attr("disabled", "disabled");
}

function validateDate(id, min_year = 1940, minimun_age = 12) {

    var valid = true;
    $(`#error_${id}`).hide();
    $(`#error_${id}`).html("");

    var year = $(`#${id}_yy`).val();
    var month = $(`#${id}_mm`).val();
    var days = $(`#${id}_dd`).val();

    var date_string = year + "/" + month + "/" + days;
    var date = Date.parse(date_string);

    var reason = "La fecha no es válida.";

    if (isNaN(date) ||
        (!(year >= min_year && year <= new Date().getFullYear())) ||
        (!(month >= 1 && month <= 12)) ||
        (!(days >= 1 && days <= 31))
    ) {
        valid = false;
    }

    if (new Date().getFullYear() - year < minimun_age) {
        valid = false;
        reason = `Se requiere de minimo ${minimun_age} años de edad.`;
    }

    var max_days = 31;

    if (month == 2) {
        max_days = 28;
        if (isLeapYear(year)) {
            max_days = 29;
        }
    } else if (month % 2 == 0) {
        max_days = 30;
    }

    if (days > max_days) {
        valid = false;
    }

    if (valid) {
        $(`#${id}`).val(date_string);
    } else {
        $(`#error_${id}`).html(reason);
        window.setTimeout(function () {
            $(`#error_${id}`).show();
        }, 0);

    }

    return valid;
}