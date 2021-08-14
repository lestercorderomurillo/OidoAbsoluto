<?php

namespace VIP\Service;

use VIP\Core\BaseObject;

abstract class AbstractService extends BaseObject
{
    function __construct(string $id = "srv")
    {
        $this->setObjectID($id);
    }

    abstract function execute();
}
