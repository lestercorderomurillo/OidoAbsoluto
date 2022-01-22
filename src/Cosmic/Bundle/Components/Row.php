<?php

namespace Cosmic\Bundle\Components;

use Cosmic\Binder\Component;

class Row extends Component
{
    public function __construct(string $columnsPosition = "top", int $padding = 0)
    {
        switch ($columnsPosition){
            case "top": $this->columnsPosition = "align-items-start"; break;
            case "center": $this->columnsPosition = "align-items-center"; break;
            case "bottom": $this->columnsPosition = "align-items-end"; break;
            default: $this->columnsPosition = "align-items-center"; break;
        }
        
        $this->padding = $padding;
    }

    public function render()
    {
        return <<<HTML
            <div class="row h-100 no-gutter {columnsPosition} p-{padding} {class}">
                {body}
            </div>
        HTML;
    }
}

publish(Row::class);