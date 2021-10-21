<?php
return
[
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
        "defaults" => ["maxWidth" => "1200px", "type" => "text"],
        "prototype" => "input",
        "inline",
        "render" => 
        <<<HTML
        <this id&name="{this:bind}" type="{this:type}" class="form-control app-field app-focuseable" 
        style="max-width: {this:maxWidth};" maxlength="64" autocomplete="{view:random}">
        <br>
        HTML,
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
        HTML,
    ],
    "radio" => [
        "required" => ["bind", "value", "text", "id"],
        "prototype" => "input",
        "inline",
        "render" =>
        <<<HTML
        <this id="{this:bind}-{this:id}" name="{this:bind}" class="p-1 ml-1 d-inline" type="radio" value="{this:value}">
        <app:label for="{this:bind}-{this:id}" classes="pl-2 d-inline">{this:text}</app:label> 
        <br><br>
        HTML,
    ],
    "date" => [
        "required" => ["bind"],
        "defaults" => ["_template" => "form-control d-inline app-textfield app-focuseable text-small"],
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