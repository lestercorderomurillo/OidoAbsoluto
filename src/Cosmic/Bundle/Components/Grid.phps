<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;
use function Cosmic\Core\Bootstrap\app;
use function Cosmic\Core\Bootstrap\publish;

class Row extends Component
{
    public function render(): string
    {
        return {{
            <div class="row">
                {body}
            </div>
        }};
    }
}

class Column extends Component
{
    public function __construct(int $size = 12, string $textPosition = "left", string $classes = "")
    {
        $this->size = $size;
        $this->textPosition = $textPosition;
        $this->classes = $classes;
    }

    public function render(): string
    {
        return {{
            <div class="pb-2 col col-12 col-sm-12 col-md-12 col-lg-12 col-xl-{size} mx-auto text-{textPosition} {classes}">
                {body}
            </div>
        }};
    }
}

class Spacing extends Component
{
    const Inline = true;

    public function __construct(int $size = 1)
    {
        $this->size = $size + 1;
    }

    public function render(): string
    {
        return {{
            <For from="0" to="{size}">
                <br>
            </For>
        }};
    }
}


class Card extends Component
{
    public function __construct(
        string $minWidth = "10px",
        string $maxWidth = "1400px",
        string $minHeight = "10px",
        string $maxHeight = "5000px",
        string $size = "12",
        string $padding = "5",
        string $fontSize = "1em",
        string $accent = "Primary",
        string $overflow = "100",
        string $id = ""
    )
    {
        $this->minWidth = $minWidth;
        $this->maxWidth = $maxWidth;
        $this->minHeight = $minHeight;
        $this->maxHeight = $maxHeight;
        $this->size = $size;
        $this->padding = $padding;
        $this->fontSize = $fontSize;
        $this->accent = $accent;
        $this->overflow = $overflow;
        $this->id = $id;
        $this->_template = "p-1 col col-12 col-sm-12 col-md-12";
    }

    public function render(): string
    {
        return {{
            <div id&name="{id}" class="{_template} col-lg-{size} col-xl-{size} mx-auto text-center" style="min-width: {minWidth}; max-width: {maxWidth}; min-height: {minHeight}; max-height: {maxHeight};">
                <div class="shadow shadow-lg text-left Accent{accent} p-{padding} rounded" style="font-size: {fontSize}; min-height: {minHeight};">
                    {body}
                </div>
            </div>
        }};
    }
}

publish([
    Row::class, 
    Column::class, 
    Spacing::class, 
    Card::class
]);
