<?php
return
    [
        "action-link" => [
            "required" => ["route"],
            "prototype" => "a",
            "id" => "[id]",
            "include" => [
                "href" => "[@url][route]",
            ],
            "new-line"
        ],
        "action-button" => [
            "required" => ["route"],
            "prototype" => "a",
            "id" => "[id]",
            "include" => [
                "href" => "[@url][route]",
            ],
            "concatElement" => [
                "prototype" => "button",
                "class" => "small text-uppercase btn v-button rounded-0 m-1 text-accent-[accent] bg-accent-[accent]",
                "type" => "button"
            ]
        ],
        "submit-button" => [
            "prototype" => "button",
            "id" => "[id]",
            "class" => "small text-uppercase btn v-button rounded-0 m-1",
            "type" => "submit"
        ],
        "script-button" => [
            "required" => ["onClick"],
            "prototype" => "button",
            "id" => "[id]",
            "class" => "small text-uppercase btn v-button rounded-0 m-1",
            "type" => "button",
            "include" => [
                "onclick" => "[onClick]()",
            ]
        ],
    ];
