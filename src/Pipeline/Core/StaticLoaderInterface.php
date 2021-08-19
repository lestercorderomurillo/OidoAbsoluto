<?php

namespace Pipeline\Core;

interface StaticLoaderInterface
{
    public static function __static(): void;
}