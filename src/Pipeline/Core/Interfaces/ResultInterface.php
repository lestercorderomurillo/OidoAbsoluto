<?php

namespace Pipeline\Core\Interfaces;

use Pipeline\HTTP\Server\ServerResponse;

interface ResultInterface
{
    public function toResponse(): ServerResponse;
}
