<?php

namespace Pipeline\Core;

use Pipeline\HTTP\Server\ServerResponse;

interface ResultInterface
{
    public function toResponse(): ServerResponse;
}
