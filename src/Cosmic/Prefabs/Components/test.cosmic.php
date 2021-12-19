<?php

namespace Cosmic\Prefabs\Components;

use Cosmic\DOM\Component;

export(View::class);

class View extends Component
{
    private string $title;
    private string $headers;
    private string $scripts;
    private string $styles;
    private string $language = "es";

    public function __construct(string $title, $language = "es")
    {
        $this->title = $title;
        $this->language = $language;
        $this->headers = app()->get("view-headers");
        $this->scripts = app()->get("view-scripts"); 
        $this->styles = app()->get("view-styles"); 
    }

    public function action1(int $new_language): void
    {
        $this->language = $new_language;

        return {{
            <div></div>
        }};

        return <<<HTML
            <div key='H21S-DFSA-12DS-DH89' class="cosmic-container">
            </div>
        HTML;
    }

    public function render(): string
    {
        return {{
            <!DOCTYPE html>
            <html lang="{ language }">
                <head>
                    <title>{ title }</title>
                    <meta style="font-size: 12px;" charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <foreach using="header" from="{ headers }">
                        <meta name="{ parent->header->name }" content="{ parent->header->content }">
                    </foreach>
                    <foreach using="script" from="{ scripts }">
                        <script src="{ parent->script }"></script>
                    </foreach>
                    <link rel="preconnect" href="https://fonts.gstatic.com">
                    <foreach using="iterator" from="{ styles }">
                        <link rel="stylesheet" href="{ parent->iterator }">
                    </foreach>
                </head>
                <div class="container">
                    { body }
                </div>
                { callbacks }
                { states }
            </html>
        }};

        return {{ <View></View> }};
    }
}
