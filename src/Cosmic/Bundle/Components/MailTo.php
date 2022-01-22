<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\InlineComponent;

class MailTo extends InlineComponent
{
    public function __construct(string $target, string $accent = "Primary")
    {
        $this->accent = $accent;
        $this->target = $target;
    }

    public function render()
    {
        return <<<HTML
            <a id="{id}" class="Link{accent} {class}" href="mailto:{target}">
                {target}
            </a>
        HTML;
    }
}

publish(MailTo::class);