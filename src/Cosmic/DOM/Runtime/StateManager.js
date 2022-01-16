/** 
 * Holds the client side state.
 */
let internalState = {};

var state = ObservableSlim.create(internalState, true, function (changes) {

  for (var i = 0; i < changes.length; i++) {

    let id = changes[i].currentPath;
    id = id.replace(".", "\\.");

    if (id.includes(".")) {
      if ($("#" + id).length) {
        $("#" + id).html(changes[i].newValue);
      }
    }

  }

});
