<?php

namespace Pipeline\Core;

abstract class Loader
{
    private static array $initialized = [];
    protected abstract static function __load(): void;

    public static function load(): void
    {
        if (!isset(Loader::$initialized[static::class])) {
            Loader::$initialized[static::class] = true;
            static::__load();
        }
    }
}
