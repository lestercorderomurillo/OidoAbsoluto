<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\InlineComponent;

class Image extends InlineComponent
{
    public function __construct(string $source)
    {
        $this->fullSource = __WEBCONTENT__ . "Images/" . $source;
    }

    public function render()
    {
        return <<<HTML
            <img class="d-inline {class}" src="{fullSource}">
            <Spacing>
        HTML;
    }
}

publish(Image::class);