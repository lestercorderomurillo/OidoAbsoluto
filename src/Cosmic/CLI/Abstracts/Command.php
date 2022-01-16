<?php

namespace Cosmic\CLI\Abstracts;

use Cosmic\Traits\ClassAwareTrait;
use Cosmic\CLI\Interfaces\CommandInterface;

abstract class Command implements CommandInterface
{
    use ClassAwareTrait;

    protected string $error;

    public abstract function verifyArguments(array $args);

    public function getCommandName(): string
    {
        return $this->getConstant("commandName");
    }

    public function getCategories()
    {
        return $this->getConstant("categoryName");
    }

    public function getLastError()
    {
        return $this->error;
    }
}
