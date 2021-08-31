<?php
return
    [
        "piano" => [
            "required" => ["pianoMode"],
            "prototype" => "div",
            "class" => "v-piano-[pianoMode] text-center"
        ],
        "piano-key" => [
            "required" => ["noteName", "keyColor", "pianoMode", "onClick"],
            "prototype" => "div",
            "id" => "[noteName]",
            "class" => "v-piano-key-[keyColor]-[pianoMode]",
            "include" => ["onclick" => "[onClick]('[noteName]')"],
            "closure"
        ],
        "piano-future-version" => [

            "instance" => function($actions, $properties){
                $properties["aws"] = 1;
                $properties["axw"] = 2;
                $properties["fsd"] = 3;

                $actions->on("view-resize", function($actions, $properties){
                    $properties["fsd"] = 3;
                    $actions->render();
                });

                $actions->reset();
                
            },
            "required" => ["pianoMode"],
            "prototype" => "div",
            "className" => "v-piano-[pianoMode]",
            "classList" => "text-center",
            "outputFields" => ["onclick" => "[onClick]('[noteName]')"],
            "concatElement" => [
                "array" => [
                    "1" => [
                        "functionParameters" => [
                            "App" => "[pianoMode]"
                        ],
                        "functionDefinition" => function($App){
                            return "<br>[a]</br>";
                        }
                    ],
                    "2" => [
                        "required" => ["pianoMode"],
                        "prototype" => "div",
                        "className" => "v-piano-[pianoMode]",
                        "concatElement" => [
                            "required" => ["pianoMode"],
                            "prototype" => "div",
                            "className" => "v-piano-[pianoMode]"
                        ]
                    ]
                ]
            ],
            "renderClosure" => true
        ]
    ];
