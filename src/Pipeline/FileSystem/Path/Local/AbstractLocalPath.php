<?php

namespace Pipeline\FileSystem\Path\Local;

use Pipeline\FileSystem\Path\AbstractPath;
use Pipeline\FileSystem\Path\Web\WebPath;

abstract class AbstractLocalPath extends AbstractPath
{
    public abstract function toWebPath(): WebPath;
}
