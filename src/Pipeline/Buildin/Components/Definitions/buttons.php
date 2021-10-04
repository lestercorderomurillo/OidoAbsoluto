<?php
return
    [
    "action-link" => [
        "required" => ["route"],
        "renderTemplate" => 
        <<<HTML
        <a href="{view:url}{this:route}">
            {this:body}
        </a>
        <br>
        HTML
    ],
    "action-button" => [
        "required" => ["route"],
        "prototype" => "button",
        "componentClass" => "button",
        "renderTemplate" => 
        <<<HTML
        <a id="{this:id}" href="{view:url}{this:route}" class="d-inline-block">
            <this class="small text-uppercase btn rounded-0 m-1 accent-{this:accent}" type="button">
                {this:body}
            </this>
        </a>
        HTML
    ],
    "submit-button" => [
        "prototype" => "button",
        "componentClass" => "button",
        "renderTemplate" => 
        <<<HTML
        <this id="{this:id}" class="small text-uppercase btn rounded-0 m-1 d-inline-block accent-{this:accent}" type="submit">
            {this:body}
        </this>
        HTML
    ],
    "js-button" => [
        "required" => ["function"],
        "prototype" => "button",
        "componentClass" => "button",
        "renderTemplate" => 
        <<<HTML
        <this id="{this:id}" class="small text-uppercase btn rounded-0 m-1 d-inline-block accent-{this:accent}" type="button" onclick="{this:function}()">
            {this:body}
        </this>
        HTML
    ]
];
