<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class RedirectButtonTooltip extends Component
{
    const Styles = [
        "Button.scss"
    ];
    
    public function __construct(string $route, string $tooltip = "", string $accent = "Secondary", string $disabled = "false")
    {
        $this->accent = $accent;
        $this->disabled = $disabled;
        $this->tooltip = $tooltip;
        $this->fullRoute = __HOST__ . $route;
    }

    public function scripts()
    {
        return <<<JS
            function awake(){
                $('[data-toggle="tooltip"]').tooltip();
            }
        JS;
    }

    public function render()
    {

        if($this->disabled === "true"){

            return <<<HTML
                <button id="{id}" title="{tooltip}" (load)="awake();" data-toggle="tooltip" data-html="true" disabled class="Button small text-uppercase btn rounded-0 m-1 d-inline-block Accent{accent}" type="button">
                    {body}
                </button>
            HTML;

        }

        return <<<HTML
            <a href="{fullRoute}" class="d-inline-block">
                <button id="{id}" title="{tooltip}" (load)="awake();" data-toggle="tooltip" data-html="true" class="Button small text-uppercase btn rounded-0 m-1 d-inline-block Accent{accent}" type="button">
                    {body}
                </button>
            </a>
        HTML;
    }
}

publish(RedirectButtonTooltip::class);
