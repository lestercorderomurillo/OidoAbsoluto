<?php

namespace Pipeline\Result;

use Pipeline\Logger\Logger;
use Pipeline\Core\Types\JSON;
use Pipeline\Core\ResultInterface;
use Pipeline\HTTP\Server\ServerResponse;
use function Pipeline\Accessors\Dependency;

class JSONResult implements ResultInterface
{
    private JSON $json;

    public function __construct(JSON $json)
    {
        $this->json = $json;
    }

    public function handle(): void
    {
        $json = ($this->json);

        $response = new ServerResponse();
        $response->addHeader("Content-Type", "application/json");
        $response->setBody($json);
        $response->send();

        Dependency(Logger::class)->debug(static::class);
    }
}
