<?php
return
[
    "form" => [
        "required" => ["route"],
        "defaults" => ["id" => "form", "method" => "post"],
        "renderTemplate" => 
        <<<HTML
        <form id="{id}" action="{url}{route}" method="post" autocomplete="{random}">
            {this.body}
        </form>
        HTML,
    ],
    "textfield" => [
        "required" => ["bind"],
        "defaults" => ["maxWidth" => "1200px", "autoComplete" => "{random}"],
        "prototype" => "input",
        "inlineComponent",
        "renderTemplate" => 
        <<<HTML
        <this id&name="{bind}" type="text" class="form-control app-field app-focuseable" 
        style="max-width: {maxWidth};" maxlength="64" autocomplete="{autoComplete}">
        <br>
        HTML,
    ],
    "passfield" => [
        "defaults" => ["bind" => "password", "maxWidth" => "1200px", "autoComplete" => "{random}"],
        "prototype" => "input",
        "inlineComponent",
        "renderTemplate" => 
        <<<HTML
        <this id&name="{bind}" type="password" class="form-control app-field app-focuseable" 
        style="max-width: {maxWidth};" maxlength="64" autocomplete="{autoComplete}">
        <br>
        HTML,
    ],
    "select" => [
        "required" => ["bind", "arrayName"],
        "prototype" => "select",
        "inlineComponent",
        "renderTemplate" => 
        <<<HTML
        <this id&name="{bind}" class="form-control app-field">
            <option value="">Seleccione una opción...</option>
            <foreach name="item" from="{arrayName}">
                <option value="{item}">{item}</option>
            </foreach>
        </this>
        <br>
        HTML,
    ],
    "radio" => [
        "required" => ["bind", "value", "text", "id"],
        "prototype" => "input",
        "inlineComponent",
        "renderTemplate" =>
        <<<HTML
        <this id="{bind}-{id}" name="{bind}" class="p-1 ml-1 d-inline" type="radio" value="{value}">
        <app:label for="{bind}-{id}" classes="pl-2 d-inline">{text}</app:label> 
        <br><br>
        HTML,
    ],
    "date" => [
        "required" => ["bind"],
        "defaults" => [
            "_template" => "form-control d-inline app-textfield app-focuseable text-small"
        ],
        "prototype" => "input",
        "inlineComponent",
        "renderTemplate" =>
        <<<HTML
        <this id&name="{bind}" type="hidden">
        <this id&name="{bind}_dd" class="{_template}" type="text" maxlength="2" style="max-width: 50px;" placeholder="DD"> / 
        <this id&name="{bind}_mm" class="{_template}" type="text" maxlength="2" style="max-width: 50px;" placeholder="MM"> / 
        <this id&name="{bind}_yy" class="{_template}" type="text" maxlength="4" style="max-width: 60px;" placeholder="YYYY">
        <br>
        HTML,
    ],
    "error" =>[
        "required" => ["bind"],
        "prototype" => "label",
        "inlineComponent",
        "renderTemplate" =>
        <<<HTML
        <this id&name="error_{bind}" for="{bind}" class="error"></this>
        <br>
        HTML,  
    ]
];