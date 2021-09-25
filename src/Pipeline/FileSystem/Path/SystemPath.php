<?php

namespace Pipeline\FileSystem\Path;

class SystemPath
{
    const SRC = __ROOT__ . "src/Pipeline/";
    const APP = __ROOT__ . "app/";
    const WEB = __ROOT__ . __WEB_NAME__ . "/";
    const LOGS = self::APP . "Logs/";

    const CONTROLLERS = self::APP . "Controllers/";
    const VIEWS = self::APP . "Views/";
    const MODELS = self::APP . "Models/";

    const MIDDLEWARES = self::APP . "middlewares/";
    const BUILDIN = self::SRC . "Buildin/";

    const COMPONENTS = self::BUILDIN . "Components/";
    const PACKAGES = self::SRC . "Packages/";
    const SCRIPTS = self::BUILDIN . "Scripts/";
    const USERCOMPONENTS = self::APP . "Components/";
    const COMMON = self::BUILDIN . "Common/";

    const DIR_INCLUDE = self::SRC . "Include/";
    const DIR_PUBLIC = self::DIR_INCLUDE . "Public/";
    const DIR_PRIVATE = self::DIR_INCLUDE . "Private/";
}
