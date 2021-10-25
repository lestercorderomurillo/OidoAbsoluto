<?php
return
[
    "shared" => [
        "render" => 
        <<<HTML
        <!---->
        HTML,
        "scripts" =>
        <<<JS
        function requireForm(id, value, bind, container){
            $(id).change(function(){
                if($(id).val() == value){
                    console.log("show " + bind);
                    $(bind).prop("disabled", false);
                    $(container).show();
                } 
                else {
                    console.log("hide " + bind);
                    $(bind).prop("disabled", true);
                    $(container).hide();
                };
            }).change(); 
        }
        JS
    ],
    "form" => [
        "required" => ["route"],
        "defaults" => ["id" => "form", "method" => "post"],
        "render" => 
        <<<HTML
        <form id="{this:id}" action="{view:url}{this:route}" method="{this:method}" autocomplete="{view:random}">
            {this:body}
        </form>
        HTML,
    ],
    "textfield" => [
        "required" => ["bind"],
        "defaults" => [
            "maxWidth" => "1200px", 
            "type" => "text",
            "requiredId" => "none",
            "requiredValue" => "none",
            "requiredPattern" => "q-",
            "container" => ""
        ],
        "prototype" => "input",
        "inline",
        "render" => 
        <<<HTML
        <this 
        id&name="{this:bind}" 
        type="{this:type}" 
        class="form-control app-field app-focuseable" 
        style="max-width: {this:maxWidth};" 
        maxlength="64" 
        autocomplete="{view:random}">
        <br>
        <if value="{this:requiredId}" startsWith="{this:requiredPattern}">
            <script type="text/javascript">
                $(function() { shared_requireForm("#{this:requiredId}", "{this:requiredValue}", "#{this:bind}", "#{this:container}"); });
            </script>
        </if>
        HTML
    ],
    "passfield" => [
        "defaults" => ["bind" => "password", "maxWidth" => "1200px"],
        "prototype" => "input",
        "inline",
        "render" => 
        <<<HTML
        <this id&name="{this:bind}" type="password" class="form-control app-field app-focuseable" 
        style="max-width: {this:maxWidth};" maxlength="64" autocomplete="{view:random}">
        <br>
        HTML,
    ],
    "select" => [
        "required" => ["bind", "arrayName"],
        "prototype" => "select",
        "defaults" => [
            "requiredId" => "none",
            "requiredValue" => "none",
            "requiredPattern" => "q-",
            "container" => ""
        ],
        "inline",
        "render" => 
        <<<HTML
        <this id&name="{this:bind}" class="form-control app-field">
            <option value="">Seleccione una opción...</option>
            <foreach name="item" from="{{this:arrayName}}">
                <option value="{item}">{item}</option>
            </foreach>
        </this>
        <br>
        <if value="{this:requiredId}" startsWith="{this:requiredPattern}">
            <script type="text/javascript">
                $(function() { shared_requireForm("#{this:requiredId}", "{this:requiredValue}", "#{this:bind}", "#{this:container}"); });
            </script>
        </if>
        HTML,
    ],
    "radio" => [
        "required" => ["bind", "value", "text", "id"],
        "prototype" => "input",
        "defaults" => [
            "requiredId" => "none",
            "requiredValue" => "none",
            "requiredPattern" => "q-",
            "container" => ""
        ],
        "inline",
        "render" =>
        <<<HTML
        <this id="{this:bind}-{this:id}" name="{this:bind}" class="p-1 ml-1 d-inline" type="radio" value="{this:value}">
        <app:label for="{this:bind}-{this:id}" classes="pl-2 d-inline">{this:text}</app:label> 
        <br><br>
        <if value="{this:requiredId}" startsWith="{this:requiredPattern}">
            <script type="text/javascript">
                $(function() { shared_requireForm("#{this:requiredId}", "{this:requiredValue}", "#{this:bind}", "#{this:container}"); });
            </script>
        </if>
        HTML,
    ],
    "date" => [
        "required" => ["bind"],
        "defaults" => [
            "_template" => 
            "form-control d-inline app-textfield app-focuseable text-small",
            "requiredId" => "none",
            "requiredValue" => "none",
            "container" => "",
        ],
        "prototype" => "input",
        "inline",
        "render" =>
        <<<HTML
        <this id&name="{this:bind}" type="hidden">
        <this id&name="{this:bind}_dd" class="{this:_template}" type="text" maxlength="2" style="max-width: 50px;" placeholder="DD"> / 
        <this id&name="{this:bind}_mm" class="{this:_template}" type="text" maxlength="2" style="max-width: 50px;" placeholder="MM"> / 
        <this id&name="{this:bind}_yy" class="{this:_template}" type="text" maxlength="4" style="max-width: 60px;" placeholder="YYYY">
        <br>
        HTML,
    ],
    "error" =>[
        "required" => ["bind"],
        "prototype" => "label",
        "inline",
        "render" =>
        <<<HTML
        <this id&name="error_{this:bind}" for="{this:bind}" class="error"></this>
        <br>
        HTML,  
    ]
];