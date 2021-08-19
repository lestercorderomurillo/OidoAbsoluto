<?php

namespace Pipeline\Controller;

abstract class ControllerBase
{
    public static function getControllerName(): string
    {
        return static::class;
    }
}
