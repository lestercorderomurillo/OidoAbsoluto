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