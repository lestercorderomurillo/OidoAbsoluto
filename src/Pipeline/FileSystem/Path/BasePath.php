<?php

namespace Pipeline\FileSystem\Path;

class BasePath
{
    const DIR_SRC = __ROOT__ . "src/Pipeline/";
    const DIR_APP = __ROOT__ . "app/";
    const DIR_WEB = __ROOT__ . __WEB_NAME__ . "/";
    const DIR_LOGS = self::DIR_APP . "logs/";
    const DIR_CONTROLLERS = self::DIR_APP . "controllers/";
    const DIR_VIEWS = self::DIR_APP . "views/";
    const DIR_MODELS = self::DIR_APP . "models/";
    const DIR_MIDDLEWARES = self::DIR_APP . "middlewares/";
    const DIR_INCLUDE = self::DIR_SRC . "Include/";
    const DIR_PUBLIC = self::DIR_INCLUDE . "Public/";
    const DIR_PRIVATE = self::DIR_INCLUDE . "Private/";
    const DIR_COMMON = self::DIR_PRIVATE . "Common/";
}
