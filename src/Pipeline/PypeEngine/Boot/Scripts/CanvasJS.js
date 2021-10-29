$(document).ready(function () {
    CanvasJS.addColorSet("shades-lime",
        [
            "#2F4F4F",
            "#008080",
            "#2E8B57",
            "#3CB371",
            "#90EE90"
        ]);
});

class CanvasJSHelper {
    static renderChart(id, options, chart = null) {
        if (chart != null) {
            $(id).remove();
        }
        var chart = new CanvasJS.Chart(id, options);
        chart.render();
        $(".canvasjs-chart-credit").hide();
        return chart;
    }
}

