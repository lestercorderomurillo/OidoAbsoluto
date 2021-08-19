<?php

namespace Pipeline\Result;

use Pipeline\Logger\Logger;
use Pipeline\Core\ResultInterface;
use Pipeline\HTTP\Server\ServerResponse;
use function Pipeline\Accessors\Dependency;

class RedirectResult implements ResultInterface
{
    private string $new_path;

    public function __construct(string $new_path)
    {
        $this->new_path = $new_path;
    }

    public function handle(): void
    {
        Dependency(Logger::class)->debug("{0} route redirection to '{1}'", [static::class, $this->new_path]);
        $response = new ServerResponse();
        $response->addHeader("Location",  __URL__ . $this->new_path);
        $response->send();
    }
}
