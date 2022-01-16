<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Container extends Component
{
    public function render()
    {
        return <<<HTML
            <div class="container-fluid {class}">
                {body}
            </div>
        HTML;
    }
}

publish(Container::class);
