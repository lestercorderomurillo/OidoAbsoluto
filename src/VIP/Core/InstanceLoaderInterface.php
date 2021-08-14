<?php

namespace VIP\Core;

interface InstanceLoaderInterface
{
    public function onInstanceLoad(): void;
}
