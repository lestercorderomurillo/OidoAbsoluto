<?php
return
    [
        "view" => [
            "prototype" => "div",
            "class" => "row"
        ],
        "col" => [
            "defaults" => ["size" => "12", "textPosition" => "left"],
            "prototype" => "div",
            "class" => "pb-2 col col-12 col-sm-12 col-md-12 col-lg-[size] col-xl-[size] mx-auto text-[textPosition]"
        ],
        "row" => [
            "prototype" => "div",
            "class" => "row"
        ],
        "spacing" => [
            "defaults" => ["size" => 1],
            "function-parameters" => [
                "size" => "[size]"
            ],
            "function" => function ($view_data, $parameters) {
                $string = "";
                for ($i = 0; $i < $parameters["size"]; $i++) {
                    $string .= "<br>";
                }
                return $string;
            }
        ],
        "card" => [
            "defaults" => [
                "maxWidth" => "1200px",
                "padding" => "5",
                "fontSize" => "12px",
                "size" => "12",
                "accent" => "primary",
                "overflow" => "100"
            ],
            "prototype" => "div",
            "id" => "[id]",
            "class" => "p-1 col col-12 col-sm-12 col-md-12 col-lg-[size] col-xl-[size] mx-auto text-center",
            "style" => "max-width: [maxWidth];",
            "concat" => [
                "prototype" => "div",
                "class" => "shadow shadow-lg text-left bg-accent-[accent] p-[padding] rounded w-100 h-[overflow]",
                "style" => "font-size: [fontSize];"
            ]
        ]
    ];
