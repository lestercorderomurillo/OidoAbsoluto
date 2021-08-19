<?php
return
    [
        "title-icon" => [
            "required" => ["text", "img"],
            "prototype" => "img",
            "id" => "[id]",
            "class" => "v-icon d-inline",
            "include" => ["src" => "[@content]img/[img]"],
            "concat" => [
                "prototype" => "h3",
                "class" => "d-inline pl-2",
                "content" => "[text]",
                "new-line" => 2,
                "closure" => true
            ]
        ],
        "title-primary" => [
            "required" => ["text"],
            "prototype" => "h4",
            "id" => "[id]",
            "class" => "font-weight-bold mb-3",
            "content" => "[text]",
            "new-line" => 1,
            "closure" => true
        ],
        "title-secondary" => [
            "required" => ["text"],
            "prototype" => "h5",
            "id" => "[id]",
            "class" => "mb-3",
            "content" => "[text]",
            "new-line" => 1,
            "closure" => true
        ],
        "title-small" => [
            "required" => ["text"],
            "prototype" => "h6",
            "id" => "[id]",
            "class" => "mb-3",
            "content" => "[text]",
            "new-line" => 1,
            "closure" => true
        ]
    ];
