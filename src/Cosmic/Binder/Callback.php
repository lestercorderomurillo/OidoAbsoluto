<?php

namespace Cosmic\Binder;

use Cosmic\Utilities\Collection;

/**
 * This class represents a callback. Can be used to create javascript functions from components.
 */
class Callback
{
    /**
     * @var string $componentName The component class to call.
     */
    private string $componentName;

    /**
     * @var string $componentAction The action to be executed.
     */
    private string $componentAction;

    
    /**
     * @var int $componentKey The component instance to reference in the client DOM.
     */
    private int $componentKey;

    /**
     * @var array $actionParameters The list of parameters to be used to build the callback.
     */
    private array $actionParameters;

    /**
     * Constructor. Create a new callback instance. Callbacks are inmutable.
     * 
     * @param string $componentName The name of the component.
     * @param string $componentAction The action to execute in the component instance.
     * @param string $componentKey The component instance to reference in the client DOM.
     * @param array $actionParameters The list of parameters to be used to build the js function.
     * 
     * @return void
     */
    public function __construct(string $componentName, string $componentAction, string $componentKey, array $actionParameters)
    {
        $this->componentName = $componentName;
        $this->componentAction = $componentAction;
        $this->componentKey = $componentKey;
        $this->actionParameters = $actionParameters;
    }

    /**
     * Return the component namespace.
     * 
     * @return string The name of the component. 
     */
    public function getComponentName(): string
    {
        return $this->componentName;
    }

    /**
     * Return the component action associated with the specified component.
     * 
     * @return string The action to execute.
     */
    public function getComponentAction(): string
    {
        return $this->componentAction;
    }

    /**
     * Return the component unique entry point token for htis callback.
     * 
     * @return string The unique token.
     */
    public function getEntryPointToken(): string
    {
        return "/_" . md5($this->componentName . "@" . $this->componentAction);
    }

    /**
     * Return a compiled javascript function that can be used to call this component from the browser.
     * 
     * @return string The compiled javascript script for this callback.
     */
    public function getJavascriptFunction(): string
    {
        $function = "function " . $this->componentName . "_" . $this->actionName . "(";
        $lastParameter = Collection::getLastElement($this->actionParameters);

        foreach ($this->actionParameters as $parameter) {

            $function .= $parameter->getName();

            if ($parameter != $lastParameter) {
                $function .= ", ";
            }
        }

        $function .= ")" . " { call('" . $this->getEntryPointToken() . "', '" . $this->componentKey . "'); }";

        return $function;
    }
}
