<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\InlineComponent;
use Cosmic\Utilities\Cryptography;

class Passfield extends InlineComponent
{
    const Styles = [
        "Field.scss"
    ];

    public function __construct(string $id, string $type = "password", string $maxWidth = "1200px")
    {
        $this->id = $id;
        $this->type = $type;
        $this->maxWidth = $maxWidth;
        $this->random = Cryptography::computeRandomKey(8);
    }

    public function render()
    {
        return <<<HTML
            <input {events} id&name="{id}" type="{type}" class="form-control Field Focuseable" style="max-width: {maxWidth};" maxlength="64" autocomplete="{random}">
            <br> 
        HTML;
    }
}

publish(Passfield::class);
