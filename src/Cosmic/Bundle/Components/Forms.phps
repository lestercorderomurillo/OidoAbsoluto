<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;
use Cosmic\Utilities\Cryptography;
use function Cosmic\Core\Bootstrap\app;
use function Cosmic\Core\Bootstrap\publish;

class Form extends Component
{
    public function __construct(string $route, string $method = "post", string $id = "form")
    {
        $this->id = $id;
        $this->method = $method;
        $this->fullRoute = __HOST__ . $route;
        $this->random = Cryptography::computeRandomKey(8);
    }

    public function render(): string
    {
        return {{
            <form id="{id}" action="{fullRoute}" method="{method}" autocomplete="{random}">
                {body}
            </form>
        }};
    }
}

class Span extends Component
{
    public function __construct(string $textPosition = "left", string $fontSize = "0.9em")
    {
        $this->textPosition = $textPosition;
        $this->fontSize = $fontSize;
    }

    public function render(): string
    {
        return {{
            <label class="w-100 text-color pt-2 pb-2 text-{textPosition}" style="font-size: {fontSize};">
                {body}
            </label>
        }};
    }
}

class Label extends Component
{
    public function __construct(string $textPosition = "left", string $fontSize = "1em", string $for = "")
    {
        $this->textPosition = $textPosition;
        $this->fontSize = $fontSize;
        $this->for = $for;
    }

    public function render(): string
    {
        return {{
            <label for="{for}" class="w-100 text-color text-{textPosition}" style="font-size: {fontSize};">
                {body}
            </label>
        }};
    }
}

class Textfield extends Component
{
    const Inline = true;

    public function __construct(string $type = "text", string $maxWidth = "1200px", string $bind)
    {
        $this->bind = $bind;
        $this->type = $type;
        $this->maxWidth = $maxWidth;
        $this->random = Cryptography::computeRandomKey(8);
    }

    public function render(): string
    {
        return {{
            <input id&name="{bind}" type="{type}" class="form-control Field Focuseable" style="max-width: {maxWidth};" maxlength="64" autocomplete="{random}"> <br> 
        }};
    }
}

class Passfield extends Component
{
    const Inline = true;

    public function __construct(string $type = "password", string $maxWidth = "1200px", string $bind = "password")
    {
        $this->bind = $bind;
        $this->type = $type;
        $this->maxWidth = $maxWidth;
        $this->random = Cryptography::computeRandomKey(8);
    }

    public function render(): string
    {
        return {{
            <input id&name="{bind}" type="{type}" class="form-control Field Focuseable" style="max-width: {maxWidth};" maxlength="64" autocomplete="{random}"> <br> 
        }};
    }
}

class SubmitButton extends Component
{
    public function __construct(string $accent = "Secondary", string $id = "")
    {
        $this->accent = $accent;
        $this->id = $id;
    }

    public function render(): string
    {
        return {{
            <button id="{id}" class="Button small text-uppercase btn rounded-0 m-1 d-inline-block Accent{accent}" type="submit">
                {body}
            </button>
        }};
    }
}

class RedirectButton extends Component
{
    public function __construct(string $route, string $accent = "Secondary", string $id = "")
    {
        $this->accent = $accent;
        $this->id = $id;
        $this->fullRoute = __HOST__ . $route;
    }

    public function render(): string
    {
        return {{
            <a id="{id}" href="{fullRoute}" class="d-inline-block">
                <button class="Button small text-uppercase btn rounded-0 m-1 d-inline-block Accent{accent}" type="button">
                    {body}
                </button>
            </a>
        }};
    }
}

class Link extends Component
{
    public function __construct(string $route, string $accent = "Secondary", string $id = "")
    {
        $this->accent = $accent;
        $this->id = $id;
        $this->fullRoute = __HOST__ . $route;
    }

    public function render(): string
    {
        return {{
            <a id="{id}" class="accent-{accent}" href="{fullRoute}">
                {body}
            </a>
        }};
    }
}

class IconifiedTitle extends Component
{
    public function __construct(string $source, string $id = "")
    {
        $this->id = $id;
        $this->fullSource = __WEBCONTENT__ . "images/" . $source;
    }

    public function render(): string
    {
        return {{
            <img id="{id}" class="d-inline" src="{fullSource}" style="width: 40px; height: auto; position: relative; margin-top: -12px;">
            <h3 class="d-inline pl-2">
                {body}
            </h3>
            <Spacing>
        }};
    }
}

publish([
    Form::class, 
    Span::class, 
    Label::class, 
    Textfield::class, 
    Passfield::class, 
    SubmitButton::class, 
    RedirectButton::class, 
    Link::class,
    IconifiedTitle::class
]);
