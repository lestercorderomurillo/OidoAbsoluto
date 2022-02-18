<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Span extends Component
{
    public function __construct(string $textPosition = "left", string $fontSize = "0.9em")
    {
        $this->textPosition = $textPosition;
        $this->fontSize = $fontSize;
    }

    public function render()
    {
        return <<<HTML
            <div class="w-100 text-color pt-2 pb-2 text-{textPosition}" style="font-size: {fontSize};">
                {body}
            </div>
        HTML;
    }
}

publish(Span::class);
