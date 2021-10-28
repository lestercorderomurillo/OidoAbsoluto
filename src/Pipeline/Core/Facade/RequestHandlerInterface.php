<?php

namespace Pipeline\Core\Facade;

use Pipeline\HTTP\Message;

interface RequestHandlerInterface
{
    public function handle($anything): Message;
}