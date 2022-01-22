<?php

namespace app\Components;

use Cosmic\Binder\InlineComponent;

class PianoKey extends InlineComponent
{
    public function __construct(string $displayMode, string $keyColor, string $noteName, string $noteText)
    {
        $this->displayMode = $displayMode;
        $this->noteName = $noteName;
        $this->noteText = $noteText;
        $this->keyColor = $keyColor;
    }

    public function render()
    {
        return <<<JS
            <If value="{displayMode}" equals="Simple">
                <div {events} id="{noteName}" class="col col-2 p-3 mt-2 card text-center font-weight-bold" style="font-size: 1.2rem; min-height:130px;">   
                    <br>
                    {noteText}
                </div>
            </If>
            <If value="{displayMode}" equals="Full">
                <div {events} id="{noteName}" class="PianoKey{keyColor}{displayMode} text-center">
                </div>
            </If>
        JS;
    }
}

publish(PianoKey::class);
