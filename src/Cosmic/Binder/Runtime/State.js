/** 
 * Holds the client side state.
 */
var componentStateStore = {};

/** 
 * Performs server side rendering using the given state object.
 * After the ajax call is performed, the response will be used to rebuild the DOM structure.
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
 */
function setState(componentKey, variableKey, value) {
    if(!componentStateStore[componentKey]){
        componentStateStore[componentKey] = {};
    }
    componentStateStore[componentKey][variableKey] = value;
}

/** 
 * Get the current client side state for the given component.
 */
function getState(componentKey) {
    return componentStateStore[componentKey];
}
