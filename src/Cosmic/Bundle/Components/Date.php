<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\InlineComponent;

class Date extends InlineComponent
{
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function render()
    {
        $this->_template = "form-control d-inline Field Focuseable text-small";

        return <<<HTML
            <input id&name="{id}" type="hidden">
            <input id&name="{id}_dd" class="{_template}" type="text" maxlength="2" style="max-width: 50px;" placeholder="DD"> / 
            <input id&name="{id}_mm" class="{_template}" type="text" maxlength="2" style="max-width: 50px;" placeholder="MM"> / 
            <input id&name="{id}_yy" class="{_template}" type="text" maxlength="4" style="max-width: 60px;" placeholder="YYYY">
        HTML;
    }
}

publish(Date::class);
