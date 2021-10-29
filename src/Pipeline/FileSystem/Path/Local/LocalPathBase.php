<?php

namespace Pipeline\FileSystem\Path\Local;

use Pipeline\FileSystem\Path\PathBase;
use Pipeline\FileSystem\Path\Web\WebPath;

abstract class LocalPathBase extends PathBase
{
    public abstract function toWebPath(): WebPath;
}
