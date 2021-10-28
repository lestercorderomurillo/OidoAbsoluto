<?php

namespace Pipeline\Core\Facade;

use Pipeline\HTTP\Server\ServerResponse;

interface ResultInterface
{
    public function toResponse(): ServerResponse;
}
