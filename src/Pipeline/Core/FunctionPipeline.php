<?php

namespace Pipeline\Core;

class FunctionPipeline
{
    private array $stages;

    public function __construct(array $stages)
    {

        $this->stages = $stages;
    }

    public function execute($instance, $result = null)
    {
        foreach ($this->stages as $stage){
            $result = $instance->$stage($result);
        }
        return $result;
    }
}
