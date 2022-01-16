<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Hint extends Component
{
    public function render()
    {
        return <<<HTML
            <label class="small">
                {body}
            </label>
        HTML;
    }
}

publish(Hint::class);
