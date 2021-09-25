<?php
return
[
    "container" => [
        "prototype" => "div",
        "renderTemplate" => 
        <<<HTML
        <this class="container">
            {this.body}
        </this>
        HTML
    ],
    "row" => [
        "prototype" => "div",
        "renderTemplate" => 
        <<<HTML
        <this class="row">
            {this.body}
        </this>
        HTML
    ],
    "col" => [
        "defaults" => ["columnSize" => "12", "textPosition" => "left"],
        "prototype" => "div",
        "renderTemplate" => 
        <<<HTML
        <this class="pb-2 col col-12 col-sm-12 col-md-12 col-lg-12 col-xl-{columnSize} mx-auto text-{textPosition} {classes}">
            {this.body}
        </this>
        HTML
    ],
    "spacing" => [
        "defaults" => ["size" => 1],
        "inlineComponent",
        "renderTemplate" => 
        <<<HTML
        <for start="1" end="{size}">
            <br>
        </for>
        HTML
    ],
    "card" => [
        "defaults" => [
            "maxWidth" => "1200px",
            "columnSize" => "12",
            "padding" => "5",
            "fontSize" => "12px",
            "accent" => "primary",
            "overflow" => "100"
        ],
        "prototype" => "div",
        "renderTemplate" => 
        <<<HTML
        <this id&name="{id}" class="p-1 col col-12 col-sm-12 col-md-12 col-lg-{columnSize} col-xl-{columnSize} mx-auto text-center" style="max-width: {maxWidth};">
            <div class="shadow shadow-lg text-left accent-{accent} p-{padding} rounded w-100 h-{overflow}" style="font-size: {fontSize};">
                {this.body}
            </div>
        </this>
        HTML
    ],
];
