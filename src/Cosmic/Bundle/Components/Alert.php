<?php

namespace Cosmic\Bundle\Components;

use Cosmic\DOM\Component;

class Alert extends Component
{
    const Styles = [
        "Alert.scss"
    ];

    public function __construct(string $type = "Warning", string $canClose = "true")
    {
        $this->type = ucfirst($type);
        $this->canClose = $canClose;

        if($this->canClose == "true"){
            $this->dismissible = "alert-dismissible";
        }else{
            $this->dismissible = __EMPTY__;
        }

        switch($type){
            case "Error": $this->strongMessage = "¡Ha ocurrido un problema!"; break;
            case "Warning": $this->strongMessage = "¡Advertencia!"; break;
            case "Info": $this->strongMessage = "¡Aviso!"; break;
            default: $this->strongMessage = "¡Excelente!"; break;
        }
    }

    public function render()
    {
        return  <<<HTML
            <div class="alert Alert{type} {dismissible} fade show">
                <If value="{canClose}" equals="true">
                    <span class="close mt-1" data-dismiss="alert" aria-label="Close">
                        <small>
                            &times;
                        </small>
                    </span>
                </If>
                <strong>{strongMessage}</strong>
                <div>
                    {body}
                </div>
            </div>
        HTML;
    }
}


publish(Alert::class);
