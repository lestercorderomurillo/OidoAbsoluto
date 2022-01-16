<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Link extends Component
{
    const Styles = [
        "Link.scss"
    ];

    public function __construct(string $route, string $accent = "Primary")
    {
        $this->accent = $accent;
        $this->fullRoute = __HOST__ . $route;
    }

    public function render()
    {
        return <<<HTML
            <a id="{id}" class="Link{accent} {class}" href="{fullRoute}">
                {body}
            </a>
        HTML;
    }
}

publish(Link::class);
