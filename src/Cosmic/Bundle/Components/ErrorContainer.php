<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\InlineComponent;

class ErrorContainer extends InlineComponent
{
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function render()
    {
        return <<<HTML
            <label id&name="error_{id}" for="{id}" class="error"></label>
            <Spacing>
        HTML;
    }
}


publish(ErrorContainer::class);
