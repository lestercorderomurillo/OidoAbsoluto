<?php

namespace Pipeline\Factory;

use Pipeline\FileSystem\Path\Web\WebPath;

class PathFactory
{
    public static function createWebPath(string $string_input): WebPath
    {
        return new WebPath(str_replace(__ROOT__, "", $string_input));
    }
}
