<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\InlineComponent;

class Spacing extends InlineComponent
{
    public function __construct(int $size = 1)
    {
        $this->size = $size + 1;
    }

    public function render()
    {
        return <<<HTML
            <For from="0" to="{size}">
                <br>
            </For>
        HTML;
    }
}

publish(Spacing::class);
