<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\InlineComponent;

class PersistentMessage extends InlineComponent
{
    public function render()
    {
        $this->text = session("alertText");
        $this->type = session("alertType");

        if ($this->text != null) {

            return <<<HTML
                <Alert type="{type}">
                    {text}
                </Alert>
            HTML;
        }
    }

    public function dispose()
    {
        session()->delete("alertText");
        session()->delete("alertType");
    }
}

publish(PersistentMessage::class);