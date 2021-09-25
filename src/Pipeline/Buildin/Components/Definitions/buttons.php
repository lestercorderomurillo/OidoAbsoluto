<?php
return
    [
    "action-link" => [
        "required" => ["route"],
        "renderTemplate" => 
        <<<HTML
        <a href="{url}{route}">
            {this.body}
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
        <a id="{id}" href="{url}{route}" class="d-inline-block">
            <this class="small text-uppercase btn rounded-0 m-1" type="button">
                {this.body}
            </this>
        </a>
        HTML
    ],
    "submit-button" => [
        "prototype" => "button",
        "componentClass" => "button",
        "renderTemplate" => 
        <<<HTML
        <this id="{id}" class="small text-uppercase btn rounded-0 m-1 d-inline-block" type="submit">
            {this.body}
        </this>
        HTML
    ],
    "js-button" => [
        "required" => ["function"],
        "prototype" => "button",
        "componentClass" => "button",
        "renderTemplate" => 
        <<<HTML
        <this id="{id}" class="small text-uppercase btn rounded-0 m-1 d-inline-block" type="button" onclick="{function}()">
            {this.body}
        </this>
        HTML
    ]
];
