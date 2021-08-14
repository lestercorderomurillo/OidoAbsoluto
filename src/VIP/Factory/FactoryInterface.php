<?php

namespace VIP\Factory;

interface FactoryInterface
{
    public static function create(string $string_input, array $array_input = []): Object;
}
