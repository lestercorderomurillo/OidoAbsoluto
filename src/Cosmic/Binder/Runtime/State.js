/** 
 * Holds the client side state.
 */
var componentStateStore = {};

/** 
 * Performs server side rendering using the given state object.
 * After the ajax call is performed, the response will be used to rebuild the DOM structure.
 * 
 * @param {string} destination The target URL to try to recover the component new state.
 * @param {any} component The componend ID to use to retrieve the current component state.
 * 
 */
function call(destination, component) {
    jQuery.ajax({
        type: "post",
        url: destination,
        data: getState(component),
        success: function (response) {
            console.log(response.componentKey);
            console.log(response.resultString);
            console.log(response.override);
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(xhr.status);
            alert(thrownError);
        }
    });
}

/** 
 * Set the state on the client side. The data should be encoded in some format to avoid avoid hacking.
 * 
 * @param {string} componentKey The key of the component.
 * @param {string} variableKey The key-value to store in the component.
 * @param {string} value The value for this variableKey.
 */
function setState(componentKey, variableKey, value) {
    if(!componentStateStore[componentKey]){
        componentStateStore[componentKey] = {};
    }
    componentStateStore[componentKey][variableKey] = value;
}

/** 
 * Get the current client side state for the given component.
 * 
 * @param {string} componentKey The key of the component.
 * 
 * @return {any} The current state of the component.
 */
function getState(componentKey) {
    return componentStateStore[componentKey];
}
