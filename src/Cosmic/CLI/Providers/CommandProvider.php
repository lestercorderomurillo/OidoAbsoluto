<?php

namespace Cosmic\CLI\Providers;

use Cosmic\Core\Abstracts\AutoProvider;
use Cosmic\CLI\Commands\CreateControllerCommand;

class CommandProvider extends AutoProvider
{
    private static array $commands = [];

    public static function boot(): void
    {
        static::$commands[] = create(CreateControllerCommand::class);
    }

    public static function provide()
    {
        return static::$commands;
    }
}
