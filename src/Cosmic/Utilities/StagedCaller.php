<?php

namespace Cosmic\Utilities;

/**
 * This helper class is used to execute a sequence of calls on the same object.
 * With this, you can easily create a call-queue for a given object, running a single
 * line of context flow for each call.
 */
class StagedCaller
{
    /**
     * @var \Closure[] $stages A collection of closures.
     */
    private array $stages;

    /**
     * Create a new function pipeline from the collection of stages given.
     * 
     * @param \Closure[] $stages A collection of closures.
     * 
     * @return void
     */
    public function __construct(array $stages)
    {
        $this->stages = $stages;
    }

    /**
     * Executes this pipeline. The instance will pass through all the registered stages.
     * On null, this function stop executing and return null too. If the pipeline manages to complete,
     * then this method will return the affected instance.
     * 
     * @param mixed|object $caller Any instance that can execute a method.
     * @param mixed $data All the required parameters to pass to the caller each stage.
     * 
     * @return mixed|null Can be anything actually, but usually it will the same instance but with modifications.
     */
    public function execute($caller, $data = null)
    {
        foreach ($this->stages as $stage){
            
            if($data == null) return null;

            $data = $caller->$stage($data);
        }

        return $data;
    }
}
