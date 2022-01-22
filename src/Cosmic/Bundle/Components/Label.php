<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Label extends Component
{
    public function __construct(string $textPosition = "left", string $fontSize = "1em", string $for = "")
    {
        $this->textPosition = $textPosition;
        $this->fontSize = $fontSize;
        $this->for = $for;
    }

    public function render()
    {
        return <<<HTML
            <label for="{for}" class="w-100 text-color text-{textPosition} {class}" style="font-size: {fontSize};">
                {body}
            </label>
        HTML;
    }
}


publish(Label::class);