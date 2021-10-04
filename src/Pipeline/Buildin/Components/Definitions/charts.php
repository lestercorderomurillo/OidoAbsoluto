<?php
return
    [
        "chart" => [
            "required" => ["title", "subTitle", "dataSource", "id"],
            "defaults" => [
                "theme" => "light2",
                "width" => "100%",
                "height" => "100%",
                "minHeight" => "450px",
                "type" => "column"
            ],
            "prototype" => "div",
            "inlineComponent",
            "renderTemplate" =>
            <<<HTML
            <this id="{this:id}" style="width: {this:width}; height: {this:height}; min-height: {this:minHeight};"></this>
            <script type="text/javascript">
                $(document).ready(function() {
                    var chart_{this:id} = null;
                    var options_{this:id} = {
                        zoomEnabled: false,
                        animationEnabled: true,
                        exportEnabled: true,
                        theme: "{this:theme}",
                        backgroundColor: "rgba(0, 0, 0, 0)",
                        title: {
                            text: "{this:title}",
                            fontFamily: "segoe",
                        },
                        subtitles: [{
                            text: "{this:subTitle}"
                        }],
                        axisY: {
                            minimum: 0,
                        },
                        data: [{
                            type: "{this:type}",
                            toolTipContent: "{y}/{yMax} ({percent})%",
                            percentFormatString: "#0.##",
                            dataPoints: {view:{this:dataSource}}
                        }]
                    };
                    chart_{this:id} = CanvasJSHelper.renderChart("{this:id}", options_{this:id}, chart_{this:id});
                    $(window).on('resize', function() {
                        clearTimeout(window.resized_finished_{this:id});
                        window.resized_finished_{this:id} = setTimeout(function() {
                            chart_{this:id} = CanvasJSHelper.renderChart("{this:id}", options_{this:id}, chart_{this:id});
                        }, 500);
                    });
                });
            </script>
            HTML,
        ]
    ];
