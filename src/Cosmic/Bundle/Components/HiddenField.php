<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\InlineComponent;
use Cosmic\Utilities\Cryptography;

class HiddenField extends InlineComponent
{
    const Styles = [
        "Field.scss"
    ];

    public function __construct(string $id, string $value)
    {
        $this->id = $id;
        $this->value = $value;
    }

    public function render()
    {
        return <<<HTML
            <input id="{id}" name="{id}" type="hidden" value="{value}">
        HTML;
    }
}

publish(HiddenField::class);
