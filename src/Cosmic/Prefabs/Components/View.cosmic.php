<?php

namespace Cosmic\Prefabs\Components;

use Cosmic\DOM\Component;

//export(Minimal::class, View::class);

class View extends Component
{
    public string $title;
    public string $language = "es";

    public function render(): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html scope="global" lang="{ language }">
                <head>
                    <title>{ title }</title>
                    <meta style="font-size: 12px;" charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <Foreach using="iterator" from="{ global.headers }">
                        <meta name="{ parent.iterator.name }" content="{ parent.iterator.content }">
                    </Foreach>
                    <Foreach using="iterator" from="{ global.scripts-bundles }">
                        <script src="{ parent.iterator }"></script>
                    </Foreach>
                    <link key="a" rel="preconnect" href="https://fonts.gstatic.com">
                    <Foreach using="iterator" from="{ global.styles-bundles }">
                        <link rel="stylesheet" href="{ parent.iterator }">
                    </Foreach>
                </head>
                <div class="container">
                    { body }
                </div>
                { global.callbacks }
                { global.states }
            </html>
        HTML;
    }
}
