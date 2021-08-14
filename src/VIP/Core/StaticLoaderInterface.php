<?php

namespace VIP\Core;

interface StaticLoaderInterface
{
    public static function onStaticLoad(): void;
}
