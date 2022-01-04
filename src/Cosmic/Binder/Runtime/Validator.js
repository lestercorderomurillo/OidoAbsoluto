class Validator {
    /**
     * Reset the current error container for a dom object.
     * 
     * @param {string} id The id of the DOM object.
     */
    static resetError(id) {
        $(`#error_${id}`).hide();
        $(`#error_${id}`).html("");
    }

    /**
     * Check if the given value is empty or not.
     * 
     * @param {string} str The string value to check.
     * 
     * @returns {string} Return true if it is empty, false otherwise.
     */
    static isEmpty(str) {
        return (!str || str.length === 0);
    }

    /**
     * Check if the given value is blank, but not necessarily empty.
     * 
     * @param {string} str The string value to check.
     * 
     * @returns {string} Return true if it is blank, false otherwise.
     */
    static isBlank(str) {
        return (!str || /^\s*$/.test(str));
    }

    /**
     * Validate the current date object given an DOM Id.
     * 
     * @param {string} domID The javascript DOM ID.
     * 
     * @returns {boolean} Return true if is valid, false otherwise.
     */
    static validateDate(domID) {

        Validator.resetError(domID);

        var errorReason = Validator.isDateValid(domID);

        if (Validator.isBlank(errorReason)) {
            $(`#${domID}`).val(Validator.getComposedDate(domID));
            return true;
        }
        
        $(`#error_${domID}`).html(errorReason);
        
        delay(() => $(`#error_${domID}`).show(), 1);

        return false;
    }

    /**
     * Return the date for this given DOM element.
     * 
     * @param {string} domID The javascript DOM ID.
     * 
     * @returns {string} The date string, that can be parsed later on.
     */
    static getComposedDate(domID) 
    {
        var year = $(`#${domID}_yy`).val();
        var month = $(`#${domID}_mm`).val();
        var days = $(`#${domID}_dd`).val();
        return year + "/" + month + "/" + days;
    }


    /**
     * Validate the current date object given an DOM Id.
     * 
     * @param {string} domID The javascript DOM ID.
     * @param {int} minYear The lowest year allowed for this date.
     * @param {int} minimunAge The lower allowed age for this date.
     * 
     * @returns {string} Return the reason for the failure, if not, the empty string.
     */
    static isDateValid(domID, minYear = 1940, minimunAge = 12) {

        var maxYear = new Date().getFullYear();
        var year = $(`#${domID}_yy`).val();
        var month = $(`#${domID}_mm`).val();
        var days = $(`#${domID}_dd`).val();
        var dateString = year + "/" + month + "/" + days;
        var dateObject = Date.parse(dateString);

        if (!Validator.isDateInRange(dateObject, year, month, days, minYear, maxYear)) {
            return `La fecha no está en el rango admitido.`;
        }

        if (!Validator.isRealDate(year, month, days)) {
            return `No es una fecha real. Verifique.`;
        }

        if (maxYear - year < minimunAge) {
            return `Se requiere de minimo ${minimunAge} años de edad.`;
        }

        return "";
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
    static isDateInRange(date, year, month, day, minYear, maxYear) {
        if (isNaN(date) ||
            (!(year >= minYear && year <= maxYear)) ||
            (!(month >= 1 && month <= 12)) ||
            (!(day >= 1 && day <= 31))
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check if the given year is a leap year.
     * 
     * @param {int} year The year number.
     * 
     * @returns {boolean} True if the year is leap, false otherwise.
     */
    static isLeapYear(year) {
        return ((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0);
    }

    /**
     * Check if the given date is even real or a fake date.
     * 
     * @param {int} year The year number.
     * @param {int} month The month number.
     * @param {int} day The day number.
     * 
     * @returns {boolean} True if the date is real, false otherwise.
     */
    static isRealDate(year, month, day) {
        var maxDays = 31;

        if (month == 2) {
            maxDays = 28;
            if (Validator.isLeapYear(year)) {
                maxDays = 29;
            }
        } else if (month % 2 == 0) {
            maxDays = 30;
        }
        if (day > maxDays) {
            return false;
        }

        return true;
    }

    /**
     * Enable the current DOM date element.
     * 
     * @param {string} domID The javascript DOM ID.
     */
    static enableDateSubComponents(domID) {
        $(`#${domID}_yy`).removeAttr("disabled");
        $(`#${domID}_mm`).removeAttr("disabled");
        $(`#${domID}_dd`).removeAttr("disabled");
    }

    /**
     * Disable the current DOM date element.
     * 
     * @param {string} domID The javascript DOM ID.
     */
    static disableDateSubComponents(domID) {
        $(`#${domID}_yy`).attr("disabled", "disabled");
        $(`#${domID}_mm`).attr("disabled", "disabled");
        $(`#${domID}_dd`).attr("disabled", "disabled");
    }
}