<?php
return
[
    "title-icon" => [
        "required" => ["text", "imageSource"],
        "prototype" => "img",
        "inlineComponent", 
        "renderTemplate" =>
        <<<HTML
        <this id="{this.id}" class="d-inline" src="{view.url}web/images/{this.imageSource}" style="width: 40px; height: auto; position: relative; margin-top: -12px;">
        <h3 class="d-inline pl-2">{this.text}</h3>
        <br><br>
        HTML
    ],
    "title-primary" => [
        "required" => ["text"],
        "prototype" => "h4",
        "inlineComponent", 
        "renderTemplate" => 
        <<<HTML
        <this id="{this.id}" class="font-weight-bold mb-3">{this.text}</this>
        <br>
        HTML
    ],
    "title-secondary" => [
        "required" => ["text"],
        "prototype" => "h6",
        "inlineComponent", 
        "renderTemplate" => 
        <<<HTML
        <this id="{this.id}" class="mb-3">{this.text}</this>
        <br>
        HTML
    ]
];
