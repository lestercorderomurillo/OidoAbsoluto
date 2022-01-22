<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class View extends Component
{
    public function __construct(string $title, string $language = "es")
    {
        $this->title = $title;
        $this->language = $language;
    }

    public function scripts()
    {
        return <<<JS
        function awake(){
            $(".error").hide();
            $(".Focuseable").keyup(function () {
                if (this.value.length == this.maxLength) {
                    $(this).next('.Focuseable').focus();
                }
            });
            $("Body").addClass('BodyLoaded');
        };
        JS;
    }

    public function render()
    {
        $this->scriptBundles = app()->get("scriptBundles");
        $this->metaBundles = app()->get("metaBundles");
        $this->styleBundles = app()->get("styleBundles");

        return <<<HTML
            <html lang="{ language }">
                <head>
                    <title>{ title }</title>
                    <meta style="font-size: 12px;" charset="UTF-8">
                    <meta name="viewport" content="width=600">
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
                <body id="{id}" (load)="awake()">
                    { body }
                </body>
                { bindings }
            </html>
        HTML;
    }
}

publish(View::class);
