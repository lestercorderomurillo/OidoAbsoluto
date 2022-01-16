<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Subtitle extends Component
{
    public function render()
    {
        return <<<HTML
            <h6 class="mb-3 {class}">
                {body}
            </h6>
        HTML;
    }
}

publish(Subtitle::class);
