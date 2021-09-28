<?php
return
    [
        "piano"  => [
            "required" => ["pianoMode"],
            "componentClass" => "piano-{this.pianoMode}",
            "renderTemplate" => 
            <<<HTML
            <this class="text-center">
                {this.body}
            </this>
            HTML,
        ],
        "piano-key"  => [
            "required" => ["noteName", "keyColor", "pianoMode", "function"],
            "inlineComponent",
            "componentClass" => "piano-key-{this.keyColor}-{this.pianoMode}",
            "renderTemplate" => 
            <<<HTML
            <this id="{this.noteName}" class="text-center" onclick="{this.function}('{this.noteName}')">
                <ifdef check="view.showKeyText">
                    {this.noteName}
                </ifdef>
            </this>
            HTML,
        ]
    ];
