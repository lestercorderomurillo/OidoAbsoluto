<?php
return
    [
        "piano"  => [
            "required" => ["pianoMode"],
            "class" => "piano-{this:pianoMode}",
            "render" => 
            <<<HTML
            <this class="text-center">
                {this:body}
            </this>
            HTML,
        ],
        "piano-key"  => [
            "required" => ["noteName", "keyColor", "pianoMode"],
            "class" => "piano-key-{this:keyColor}-{this:pianoMode}",
            "inline",
            "render" => 
            <<<HTML
            <this id="{this:noteName}" class="text-center" onclick="selectNote('{this:noteName}')">
                <if value="{view:showKeyText}">
                    {this:noteName}
                </if>
            </this>
            HTML,
        ]
    ];
