var stateStore = {};

window.state = function(id, property, assign = "null") {
    if(assign !== "null"){
        if(!stateStore[id]){
            stateStore[id] = {};
            stateStore[id][property] = assign;
        }else{
            stateStore[id][property] = assign;
        }
    }else{
        return stateStore[id][property];
    }
}