$(document).ready(function() {
    var arguments = $('script[src*=HotReload]');

    var enabled = arguments.attr('data-enabled');
    var origin = arguments.attr('data-origin');
    var timeout = arguments.attr('data-timeout');

    if (enabled == false){
        var hotreload = HotReload(origin, timeout);
        hotreload.sync();
    }
});

class HotReload {

    constructor(origin, timeout){
        this.origin = origin;
        this.timeout = timeout;
    }

    sync(){
        window.setTimeout(function() {

            $.ajax({
                type: "get",
                url: this.origin + "__HOTSWAP",
                contentType: 'application/x-www-form-urlencoded',
                dataType: "JSON",
                data: {
                    page: $("meta[name=page]").attr('content'),
                    timestamp: $("meta[name=timestamp]").attr('content')
                },
                success: function(response) {
                    if (response.needUpdate == true && response.isEnabled == true) {
                        location.reload();
                    }else if(response.isEnabled == true){
                        HotReload.sync();
                    }
                },
            });

        }, this.timeout);

    }

}