<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class FluidRow extends Component
{
    public function __construct(int $padding = 0)
    {
        $this->padding = $padding;
    }

    public function render()
    {
        return <<<HTML
            <div class="row no-gutter p-{padding} {class}">
                {body}
            </div>
        HTML;
    }
}

publish(FluidRow::class);
