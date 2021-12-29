<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;
use function Cosmic\Core\Bootstrap\app;
use function Cosmic\Core\Bootstrap\publish;

class View extends Component
{
    const Styles = [
        "Forms.scss"
    ];

    public function __construct(string $title, string $language = "es")
    {
        $this->title = $title;
        $this->language = $language;
        $this->scriptBundles = app()->get("scriptBundles");
        $this->metaBundles = app()->get("metaBundles");
        $this->styleBundles = app()->get("styleBundles");
    }

    public function render(): string
    {
        return {{
            <html lang="{ language }">
                <head>
                    <title>{ title }</title>
                    <meta style="font-size: 12px;" charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <Foreach from="{ metaBundles }" using="meta">
                        <meta name="{ parent.meta.name }" content="{ parent.meta.content }">
                    </Foreach>
                    <Foreach from="{ scriptBundles }" using="script">
                        <script src="{ parent.script }"></script>
                    </Foreach>
                    <Foreach from="{ styleBundles }" using="style">
                        <link rel="stylesheet" href="{ parent.style }">
                    </Foreach>
                </head>
                <body class="Container">
                    <br>
                    { body }
                </body>
            </html>
        }};
    }
}

publish(View::class);
