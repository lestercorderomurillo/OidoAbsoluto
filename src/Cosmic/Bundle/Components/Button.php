<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Button extends Component
{
    const Styles = [
        "Button.scss"
    ];

    public function __construct(string $accent = "Secondary")
    {
        $this->accent = $accent;
    }

    public function render()
    {
        return <<<HTML
            <button {events} id="{id}" class="Button small text-uppercase btn rounded-0 m-1 d-inline-block Accent{accent}" type="button">
                {body}
            </button>
        HTML;
    }
}

publish(Button::class);
