<?php

namespace Cosmic\HPHP;

use Cosmic\Traits\ClassAwareTrait;
use Cosmic\Traits\ValuesGetterTrait;
use Cosmic\Traits\ValuesSetterTrait;
use Cosmic\Utilities\Strings;

/**
 * This class represents a cosmic component. Should be extended to create new components.
 */
abstract class Component{

    use ClassAwareTrait;
    use ValuesSetterTrait;
    use ValuesGetterTrait;

    private string $className;

    public function __construct($className){

        $reflectionClass = new \ReflectionClass($className);
        if ($reflectionClass->getConstant("Name") !== false) {
            $className = $reflectionClass->getConstant("Name");
        }
        $this->className = $className;
    }

    /**
     * Return the exportable component name for the given class name.
     *
     * @param string $className The class name who will be exported later.
     * @return string The ready to use exportable component name.
     */
    public function getExportName(): string
    {
        return Strings::getClassBaseName($this->className);
    }
    
    /**
     * render
     *
     * @return Node[]|Node|null
     */
    public abstract function render();
}
    