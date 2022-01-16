<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Title extends Component
{
    public function render()
    {
        return <<<HTML
            <h4 class="mb-3 {class}">
                {body}
            </h4>
        HTML;
    }
}

publish(Title::class);
