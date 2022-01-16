<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Card extends Component
{
    public function __construct(
        string $minWidth = "10px",
        string $maxWidth = "3200px",
        string $minHeight = "10px",
        string $maxHeight = "5000px",
        string $padding = "5",
        string $fontSize = "1em",
        string $accent = "Primary",
        string $textPosition = "center"
    ) {
        $this->minWidth = $minWidth;
        $this->maxWidth = $maxWidth;
        $this->minHeight = $minHeight;
        $this->maxHeight = $maxHeight;
        $this->padding = $padding;
        $this->fontSize = $fontSize;
        $this->accent = $accent;
        $this->textPosition = $textPosition;
    }


    public function render()
    {
        return <<<HTML
            <div id&name="{id}" class="text-{textPosition} mx-auto {class}" style="min-width: {minWidth}; max-width: {maxWidth}; min-height: {minHeight}; max-height: {maxHeight};">
                <div class="shadow shadow-lg text-left Accent{accent} p-{padding} rounded h-100" style="font-size: {fontSize}; min-height: {minHeight};">
                    {body}
                </div>
            </div>
        HTML;
    }
}

publish(Card::class);
