<?php

namespace Pipeline\HTTP\Common;

use Pipeline\HTTP\Message;

interface RequestHandlerInterface
{
    public function handle($anything): Message;
}