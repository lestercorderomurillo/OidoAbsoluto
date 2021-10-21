<?php
return
[
    "container" => [
        "prototype" => "div",
        "render" => 
        <<<HTML
        <this class="container">
            {this:body}
        </this>
        HTML
    ],
    "row" => [
        "prototype" => "div",
        "render" => 
        <<<HTML
        <this class="row">
            {this:body}
        </this>
        HTML
    ],
    "col" => [
        "defaults" => ["columnSize" => "12", "textPosition" => "left"],
        "prototype" => "div",
        "render" => 
        <<<HTML
        <this class="pb-2 col col-12 col-sm-12 col-md-12 col-lg-12 col-xl-{this:columnSize} mx-auto text-{this:textPosition} {this:classes}">
            {this:body}
        </this>
        HTML
    ],
    "spacing" => [
        "defaults" => ["size" => 1],
        "inline",
        "render" => 
        <<<HTML
        <for start="1" end="{this:size}">
            <br>
        </for>
        HTML
    ],
    "card" => [
        "defaults" => [
            "minWidth" => "10px",
            "maxWidth" => "1400px",
            "minHeight" => "10px",
            "maxHeight" => "5000px",
            "columnSize" => "12",
            "padding" => "5",
            "fontSize" => "12px",
            "accent" => "primary",
            "overflow" => "100",
            "_template" => "p-1 col col-12 col-sm-12 col-md-12",
        ],
        "prototype" => "div",
        "render" => 
        <<<HTML
        <this id&name="{this:id}" class="{this:_template} col-lg-{this:columnSize} col-xl-{this:columnSize} mx-auto text-center" style="min-width: {this:minWidth}; max-width: {this:maxWidth}; min-height: {this:minHeight}; max-height: {this:maxHeight};">
            <div class="shadow shadow-lg text-left accent-{this:accent} p-{this:padding} rounded w-100 h-{this:overflow}" style="font-size: {this:fontSize}">
                {this:body}
            </div>
        </this>
        HTML
    ],
];
