<?php
return
    [
    "action-link" => [
        "required" => ["route"],
        "render" => 
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
        "class" => "button",
        "render" => 
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
        "class" => "button",
        "render" => 
        <<<HTML
        <this id="{this:id}" class="small text-uppercase btn rounded-0 m-1 d-inline-block accent-{this:accent}" type="submit">
            {this:body}
        </this>
        HTML
    ],
    "script-button" => [
        "required" => ["onclick"],
        "prototype" => "button",
        "class" => "button",
        "render" => 
        <<<HTML
        <this id="{this:id}" class="small text-uppercase btn rounded-0 m-1 d-inline-block accent-{this:accent}" type="button" onclick="{this:onclick}">
            {this:body}
        </this>
        HTML
    ]
];
