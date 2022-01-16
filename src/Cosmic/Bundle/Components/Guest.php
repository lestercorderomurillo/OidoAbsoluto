<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Authorization;
use Cosmic\Binder\Component;

class GuestMiddleware extends Component
{
    public function render()
    {

        if (!Authorization::isLogged()) {

            return <<<HTML
                {body}
            HTML;
        }
    }
}

publish(GuestMiddleware::class);
