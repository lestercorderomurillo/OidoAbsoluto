<?php
return
[
    "title-icon" => [
        "required" => ["text", "imageSource"],
        "prototype" => "img",
        "inlineComponent", 
        "renderTemplate" =>
        <<<HTML
        <this id="{id}" class="d-inline" src="{url}web/images/{imageSource}" style="width: 40px; height: auto; position: relative; margin-top: -12px;">
        <h3 class="d-inline pl-2">{text}</h3>
        <br><br>
        HTML
    ],
    "title-primary" => [
        "required" => ["text"],
        "prototype" => "h4",
        "renderTemplate" => 
        <<<HTML
        <this id="{id}" class="font-weight-bold mb-3">{text}</this>
        <br>
        HTML
    ],
    "title-secondary" => [
        "required" => ["text"],
        "prototype" => "h6",
        "renderTemplate" => 
        <<<HTML
        <this id="{id}" class="mb-3">{text}</this>
        <br>
        HTML
    ]
];
