<?php

namespace Cosmic\Core\Applications;

use Psr\Log\LogLevel;
use Cosmic\CLI\Abstracts\Command;
use Cosmic\CLI\Providers\CommandProvider;
use Cosmic\Core\Abstracts\Application;
use Cosmic\Core\Abstracts\DIContainer;
use Cosmic\Utilities\Collections;

class ConsoleApplication extends Application
{
    protected string $framework = "Cosmic";

    protected string $version = __VERSION__;

    private array $arguments;

    /**
     * @inheritdoc
     */
    public function boot(): void
    {
        $this->container = (!isset($this->container)) ? new DIContainer() : $this->container;
        cout("$this->framework Console Environment $this->version is starting...", [], LogLevel::INFO);

        parent::boot();
        CommandProvider::boot();
        
        cout("$this->framework Console Environment $this->version is ready...", [], LogLevel::INFO);
    }

    /**
     * @inheritdoc
     */
    public function run(): int
    {
        $commands = CommandProvider::provide();

        foreach ($commands as $command) {

            if ($this->matchCommand($command, $this->arguments) == true) {
                return $command->execute(...array_slice($this->arguments, 2));
            }
        }

        return 0;
    }

    /**
     * Entry point for console commands.
     *
     * @param  mixed $arguments The arguments to pass to the command.
     * @return void
     */
    public function __construct($arguments)
    {
        $this->arguments = Collections::mapValues($arguments, function ($value) {
            return strtolower($value);
        });
    }

    /**
     * Return true if the command is supported for the given arguments.
     * 
     * @return bool True when the command matches the given arguments.
     */
    public static function matchCommand(Command $command, array $arguments): bool
    {

        $categories = $command->getCategories();
        $matchCategory = false;

        if (is_array($categories)) {

            foreach ($categories as $category) {
                if (strtolower($category) == strtolower($arguments[0])) {
                    $matchCategory = true;
                    break;
                }
            }
        } else if (is_string($categories)) {

            if ($categories === $arguments[0]) {
                $matchCategory = true;
            }
        }

        $matchAction = strtolower($command->getCommandName()) == strtolower($arguments[1]);

        if ($matchCategory && $matchAction) {

            if ($command->verifyArguments($arguments)) {
                return true;
            }
        }

        return false;
    }
}
