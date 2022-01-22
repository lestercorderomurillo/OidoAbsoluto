<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Column extends Component
{
    public function __construct($size = "auto", $xlSize = null, $mediumSize = 12, $smallSize = 12, int $padding = 2, string $textPosition = "center")
    {
        $this->xlSize = $xlSize;
        if($this->xlSize == null){
            $this->xlSize = $size;
        }
        $this->size = $size;
        $this->mediumSize = $mediumSize;
        $this->smallSize = $smallSize;
        $this->padding = $padding;
        $this->textPosition = $textPosition;
    }

    public function render()
    {
        if($this->size == "auto"){
            return <<<HTML
                <div class="col col-auto text-{textPosition} mx-auto p-{padding} {class}">
                    {body}
                </div>
            HTML;
        }

        return <<<HTML
            <div class="col col-{smallSize} col-sm-{smallSize} col-md-{mediumSize} col-lg-{size} col-xl-{xlSize} text-{textPosition} p-{padding} mx-auto {class}">
                {body}
            </div>
        HTML;
    }
}

publish(Column::class);