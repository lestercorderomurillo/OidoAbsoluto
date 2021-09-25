<?php
return
[
    "span" => [
        "defaults" => ["fontSize" => "12px", "textPosition" => "left"],
        "prototype" => "label",
        "renderTemplate" => 
        <<<HTML
        <this class="w-100 text-color pb-2 text-{textPosition}" style="font-size: {fontSize};">
            {this.body}
        </this>
        HTML,
    ],
    "label" => [
        "defaults" => ["fontSize" => "12px"],
        "prototype" => "label",
        "renderTemplate" => 
        <<<HTML
        <label for="{for}" class="w-100 text-color {classes}" style="font-size: {fontSize};">
            {this.body}
        </label>
        HTML,
    ],
    "hint" => [
        "renderTemplate" => 
        <<<HTML
        <label class="small">
            {this.body}
        </label>
        HTML,
    ],
    "alert" => [
        "defaults" => ["type" => "warning"],
        "renderTemplate" => 
        <<<HTML
        <div class="alert alert-{type} alert-dismissible fade show">
            <strong>Oops!</strong>{this.body}
            <span data-dismiss="alert" aria-label="Close">
                <small>
                    &times;
                </small>
                <br>
            </span>
        </div>
        HTML,
    ],
];