<?php

namespace VIP\Factory;

use VIP\Component\Component;

class ComponentFactory implements FactoryInterface
{
    public static function create(string $string_input, array $array_input = []): Component
    {
        $object = HTMLFactory::create($string_input, $array_input);
        $component = new Component($object->getDOMTag(), $object->getAttributes());
        $component->setClosure($object->isClosure());
        return $component;
    }
}
