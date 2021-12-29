class Validator{

    /**
     * Reset the current error container for a dom object.
     * 
     * @param {string} id The id of the DOM object.
     */
    static resetError(id){
        $(`#error_${id}`).hide();
        $(`#error_${id}`).html("");
    }

    /**
     * Check if the given date is within the given range.
     * 
     * @param {string} date The javascript date number.
     * @param {int} year The year number.
     * @param {int} month The month number.
     * @param {int} day The day number.
     * @param {int} minYear The minimum allowed value for the given year.
     * @param {int} maxYear The maximum allowed value for the given year.
     * 
     * @returns {boolean} Return true if the date is in the range, false otherwise.
     */
    static dateInRange(date, year, month, day, minYear, maxYear){
        if (isNaN(date) ||
        (!(year >= minYear && year <= maxYear)) ||
        (!(month >= 1 && month <= 12)) ||
        (!(day >= 1 && day <= 31))
        ) {
           return false;
        }
        return true;
    }

    static validateDate(domID, minYear = 1940, minimunAge = 12) {

        var valid = true;
        var maxYear = new Date().getFullYear();

        PypeValidator.resetError(domID);

        var year = $(`#${domID}_yy`).val();
        var month = $(`#${domID}_mm`).val();
        var days = $(`#${domID}_dd`).val();

        var dateString = year + "/" + month + "/" + days;
        var dateObject = Date.parse(dateString);

        var reason = "La fecha no es válida.";

        if (!Validator.dateInRange(dateObject, year, month, days, minYear, maxYear)){
            valid = false;
            reason = `La fecha no esta en el rango correcto.`;
        }

        if (valid && (new Date().getFullYear() - year < minimunAge)) {
            valid = false;
            reason = `Se requiere de minimo ${minimunAge} años de edad.`;
        }

        if (valid && !PypeValidator.isRealDate(year, month, days)){
            valid = false;
            reason = `No es una fecha real. Verifique.`;
        }

        if (valid) {

            $(`#${domID}`).val(dateString);

        } else {

            $(`#error_${domID}`).html(reason);
            $(`#error_${domID}`).show();

            /*var renderer = new PypeRenderer();
            renderer.render(function() {
                $(`#error_${domID}`).show();
            });*/

        }

        return valid;
    }

    static isLeapYear(year) {
        return ((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0);
    }

    static isRealDate(year, month, days) {
        var maxDays = 31;
        if (month == 2) {
            maxDays = 28;
            if (Validator.isLeapYear(year)) {
                maxDays = 29;
            }
        } else if (month % 2 == 0) {
            maxDays = 30;
        }
        if (days > maxDays) {
            return false;
        }
        return true;
    }

    static enableDate(id) {
        $(`#${id}_yy`).removeAttr("disabled");
        $(`#${id}_mm`).removeAttr("disabled");
        $(`#${id}_dd`).removeAttr("disabled");
    }

    static disableDate(id) {
        $(`#${id}_yy`).attr("disabled", "disabled");
        $(`#${id}_mm`).attr("disabled", "disabled");
        $(`#${id}_dd`).attr("disabled", "disabled");
    }
}