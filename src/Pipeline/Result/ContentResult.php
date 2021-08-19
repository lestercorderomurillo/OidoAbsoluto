<?php

namespace Pipeline\Result;

use Pipeline\Core\ResultInterface;
use Pipeline\Logger\Logger;
use Pipeline\HTTP\Server\ServerResponse;
use function Pipeline\Accessors\Dependency;

class ContentResult implements ResultInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = "$value";
    }

    public function handle(): void
    {
        $response = new ServerResponse();
        $response->setBody($this->value);
        $response->send($this->value);

        Dependency(Logger::class)->debug(static::class);
    }
}
