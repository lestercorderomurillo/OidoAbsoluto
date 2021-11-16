<?php

namespace Pipeline\Core\Interfaces;

use Pipeline\HTTP\Message;

interface RequestHandlerInterface
{
    public function handle($message): Message;
}