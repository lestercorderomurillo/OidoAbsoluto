class PypeValidator{

    static resetError(id){
        $(`#error_${id}`).hide();
        $(`#error_${id}`).html("");
    }

    static dateInRange(date, year, month, days, min_year, max_range){
        if (isNaN(date) ||
        (!(year >= min_year && year <= max_range)) ||
        (!(month >= 1 && month <= 12)) ||
        (!(days >= 1 && days <= 31))
        ) {
           return false;
        }
        return true;
    }

    static validateDate(pype_id, min_year = 1940, minimun_age = 12) {

        var valid = true;
        var max_year = new Date().getFullYear();

        PypeValidator.resetError(pype_id);

        var year = $(`#${pype_id}_yy`).val();
        var month = $(`#${pype_id}_mm`).val();
        var days = $(`#${pype_id}_dd`).val();

        var date_string = year + "/" + month + "/" + days;
        var date_object = Date.parse(date_string);

        var reason = "La fecha no es válida.";

        if (!PypeValidator.dateInRange(date_object, year, month, days, min_year, max_year)){
            valid = false;
            reason = `La fecha no esta en el rango correcto.`;
        }

        if (valid && (new Date().getFullYear() - year < minimun_age)) {
            valid = false;
            reason = `Se requiere de minimo ${minimun_age} años de edad.`;
        }

        if (valid && !PypeValidator.isRealDate(year, month, days)){
            valid = false;
            reason = `No es una fecha real. Verifique.`;
        }

        if (valid) {

            $(`#${pype_id}`).val(date_string);

        } else {

            $(`#error_${pype_id}`).html(reason);

            var renderer = new PypeRenderer();
            renderer.render(function() {
                $(`#error_${pype_id}`).show();
            });

        }

        return valid;
    }

    static isLeapYear(year) {
        return ((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0);
    }

    static isRealDate(year, month, days) {
        var max_days = 31;
        if (month == 2) {
            max_days = 28;
            if (PypeValidator.isLeapYear(year)) {
                max_days = 29;
            }
        } else if (month % 2 == 0) {
            max_days = 30;
        }
        if (days > max_days) {
            return false;
        }
        return true;
    }

    static enableDate(id) {
        $(`#${id}_yy`).removeAttr("disabled");
        $(`#${id}_mm`).removeAttr("disabled");
        $(`#${id}_dd`).removeAttr("disabled");
    }

    static parseDateForSubmit(id) {
        $(`#${id}_yy`).attr("disabled", "disabled");
        $(`#${id}_mm`).attr("disabled", "disabled");
        $(`#${id}_dd`).attr("disabled", "disabled");
    }
}