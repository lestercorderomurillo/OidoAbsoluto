<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\InlineComponent;

class Select extends InlineComponent
{
    public function __construct(array $from, string $id)
    {
        $this->id = $id;
        $this->from = $from;
    }

    public function render()
    {
        return <<<HTML
            <select {events} id&name="{id}" class="form-control Field">
                <option value="">%%selectOne%%</option>
                <Foreach using="item" from="{from}">
                    <option value="{parent.item}">{parent.item}</option>
                </Foreach>
            </select>
            <Spacing>
        HTML;
    }
}

publish(Select::class);
