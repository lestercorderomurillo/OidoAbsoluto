<?php
return
    [
        "view" => [
            "required" => ["title"],
            "prototype" => "body",
            "renderTemplate" =>
            <<<HTML
            <!DOCTYPE html>
            <html lang='en'>
                <head>
                    <title>{this.title}</title>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <foreach name="header" from="view.headers">
                        <meta name="{header.name}" content="{header.content}">
                    </foreach>
                    <foreach name="script" from="view.scripts">
                        <script type="text/javascript" src="{script}"></script>
                    </foreach>
                    <link rel='preconnect' href='https://fonts.gstatic.com'>
                    <foreach name="style" from="view.styles">
                        <link rel="stylesheet" href='{style}'>
                    </foreach>
                </head>
                <this class="container">
                    <br>
                    {this.body}
                </this>
            </html>
            HTML,
        ]
    ];
