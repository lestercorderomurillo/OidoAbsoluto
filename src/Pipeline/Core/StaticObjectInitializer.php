<?php

namespace Pipeline\Core;

abstract class StaticObjectInitializer
{
    private static array $initialized = [];
    protected abstract static function __initialize(): void;

    public function initializeOnce(): void
    {
        if (!isset(StaticObjectInitializer::$initialized[static::class])) {
            StaticObjectInitializer::$initialized[static::class] = true;
            static::__initialize();
        }
    }

    public static function invokeInitialize(): void
    {
        if (!isset(StaticObjectInitializer::$initialized[static::class])) {
            StaticObjectInitializer::$initialized[static::class] = true;
            static::__initialize();
        }
    }
}
