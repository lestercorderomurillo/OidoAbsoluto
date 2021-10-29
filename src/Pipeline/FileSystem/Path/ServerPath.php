<?php

namespace Pipeline\FileSystem\Path;

class ServerPath
{
    const SRC = __ROOT__ . "src/Pipeline/";
    const APP = __ROOT__ . "app/";
    const WEB = __ROOT__ . __WEB_NAME__ . "/";
    const LOGS = self::APP . "Logs/";
    const CONTROLLERS = self::APP . "Controllers/";
    const VIEWS = self::APP . "Views/";
    const MODELS = self::APP . "Models/";
    const MIDDLEWARES = self::APP . "middlewares/";
    const PREFABS = self::SRC . "Prefabs/";
    const COMPONENTS = self::PREFABS . "Components/";
    const PACKAGES = self::SRC . "Packages/";
    const SCRIPTS = self::PREFABS . "Scripts/";
    const USERCOMPONENTS = self::APP . "Components/";
    const COMMON = self::PREFABS . "Common/";
}
