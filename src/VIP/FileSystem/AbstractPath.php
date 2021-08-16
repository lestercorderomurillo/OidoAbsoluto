<?php

namespace VIP\FileSystem;

use Psr\Log\LoggerAwareTrait;
use function VIP\Core\Logger;

abstract class AbstractPath
{
    use LoggerAwareTrait;

    const DIR_SRC = __ROOT__ . "src/VIP/";
    const DIR_APP = __ROOT__ . "app/";
    const DIR_WEB = __ROOT__ . __WEB_NAME__ . "/";
    const DIR_LOGS = self::DIR_APP . "logs/";
    const DIR_CONTROLLERS = self::DIR_APP . "controllers/";
    const DIR_VIEWS = self::DIR_APP . "views/";
    const DIR_MODELS = self::DIR_APP . "models/";
    const DIR_MIDDLEWARES = self::DIR_APP . "middlewares/";
    const DIR_INCLUDE = self::DIR_SRC . "Include/";
    const DIR_PUBLIC = self::DIR_INCLUDE . "public/";
    const DIR_PRIVATE = self::DIR_INCLUDE . "private/";
    const DIR_COMMON = self::DIR_PRIVATE . "common/";

    protected string $base;
    protected string $path;

    public function __construct(string $base)
    {
        $this->base = $base;
        $this->setLogger(Logger());
    }

    public abstract function toString(): string;
}
