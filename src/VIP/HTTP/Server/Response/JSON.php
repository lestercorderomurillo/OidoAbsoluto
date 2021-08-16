<?php

namespace VIP\HTTP\Server\Response;

use VIP\Controller\BaseController;
use VIP\HTTP\Server\Response\AbstractResponse;

class JSON extends AbstractResponse{

    private int $hints;
    private $value;

    public function __construct($value, int $hints = 0)
    {
        $this->value = $value;
        $this->hints = $hints;
    }

    public function addHint(int $hint){
        $this->hints += $hint;
        return $this;
    }

    protected function handleOperation(){
        $json = addslashes(json_encode($this->value, $this->hints));
        $this->logger->debug(
            "{0} responded with '{1}' : '{2}'",
            [
                BaseController::getFQCurrentControllerName(),
                $this->getStatusCode(),
                $json
            ]
        );

        echo($json);
    }

    public function toString(){
        return addslashes(json_encode($this->value, $this->hints));
    }

}