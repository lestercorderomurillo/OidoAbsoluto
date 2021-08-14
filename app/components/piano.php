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
        ]
    ];
