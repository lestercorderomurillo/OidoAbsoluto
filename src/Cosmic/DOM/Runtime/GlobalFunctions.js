const global = () => {
    return state["_globalComponent_"];
}

const attach = (id, eventName, fn) => {
    $("#" + id).on(eventName, fn);
}

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

function sleep(milliseconds) {
    return new Promise(resolve => setTimeout(resolve, milliseconds))
}



/**
 * Returns a number whose value is limited to the given range.
 *
 * @param {int} value The number to limit the value to.
 * @param {int} min The lower boundary of the output range.
 * @param {int} max The upper boundary of the output range.
 * @returns {int} A number in the range [min, value, max].
 */
function clamp(value, min, max) {
    if (value < min) return min;
    if (value > max) return max;
    return value;
}

/**
 * Check if some error has ocurred.
 * 
 */
async function allComponentsValidated() {
    await sleep(3);
    return !global.error;
}

/**
 * Check if some error has ocurred.
 * 
 */
 function hasValidationErrors() {
    return global.error;
}


/**
 * Reset the current error container.
 * 
 * @param {string} id The id of the DOM object.
 */
function resetValidationError(id) {
    document.getElementById(`error_${id}`).style.display = 'none';
    document.getElementById(`error_${id}`).innerHTML = '';
    global.lastError = false;
}



/**
 * Set the current error container with the given message.
 * 
 * @param {string} id The id of the DOM object.
 * @param {string} message The message to display.
 */
function setValidationError(id, message) {

    delay(function () {
        document.getElementById(`error_${id}`).style.display = 'block';
        document.getElementById(`error_${id}`).innerHTML = message;
        global.error = true;
    }, 1);

}

/**
 * Check if the given value is completely empty or not.
 * 
 * @param {string} str The string value to analyze.
 * @returns {string} Return true if it is empty, false otherwise.
 */
function isStringEmpty(str) {
    return (!str || str.length === 0);
}

/**
 * Check if the given string value is blank or empty.
 * 
 * @param {string} str The string value to analyze.
 * @returns {string} Return true if it is blank, false otherwise.
 */
function isStringBlank(str) {
    return (!str || /^\s*$/.test(str));
}