<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class IconifiedTitle extends Component
{
    public function __construct(string $source)
    {
        $this->fullSource = __WEBCONTENT__ . "Images/" . $source;
    }

    public function render()
    {
        return <<<HTML
            <img class="d-inline" src="{fullSource}" style="width: 40px; height: auto; position: relative; margin-top: -12px;">
            <h3 class="d-inline pl-2">
                {body}
            </h3>
            <Spacing>
        HTML;
    }
}

publish(IconifiedTitle::class);
