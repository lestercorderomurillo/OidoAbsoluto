var arguments = $('script[src*=Hotswap]');

var enabled = arguments.attr('data-enabled');
var origin = arguments.attr('data-origin');
var timeout = arguments.attr('data-timeout');

if (enabled == false){
    var hotswap = Hotswap(origin, timeout);
    hotswap.sync();
}

class Hotswap {

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
                        Hotswap.sync();
                    }
                },
            });

        }, this.timeout);

    }

}