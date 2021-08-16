<?php

namespace VIP\Factory;

use VIP\FileSystem\WebPath;

class WebPathFactory implements FactoryInterface
{
    public static function create(string $string_input, array $array_input = []): WebPath
    {
        return new WebPath(str_replace(__ROOT__, "", $string_input));
    }
}
