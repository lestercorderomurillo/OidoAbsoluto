<?php

namespace VIP\FileSystem;

abstract class AbstractLocalPath extends AbstractPath
{
    public abstract function toWebPath(): WebPath;
}
