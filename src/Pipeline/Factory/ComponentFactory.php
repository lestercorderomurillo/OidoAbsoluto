<?php

namespace Pipeline\Factory;

use Pipeline\Component\Component;

class ComponentFactory
{
    public static function create(string $string_input, array $array_input = [])
    {
        $placeholder = HTMLFactory::create($string_input, $array_input);
        $component = new Component($placeholder->getDOMTag(), $placeholder->getAttributes());
        $component->setClosure($placeholder->isClosure());
        return $component;
    }
}
