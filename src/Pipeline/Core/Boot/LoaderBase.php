<?php

namespace Pipeline\Core\Boot;

abstract class LoaderBase
{
    private static array $initialized = [];
    protected abstract static function __load(): void;

    public static function load(): void
    {
        if (!isset(LoaderBase::$initialized[static::class])) {
            LoaderBase::$initialized[static::class] = true;
            static::__load();
        }
    }
}
