<?php

namespace Cosmic\CLI\Interfaces;

interface CommandInterface
{
    /** 
     * Must return a the command name for matching.
     * 
     * @return string 
     **/
    public function getCommandName(): string;

    /** 
     * Must return a single category or a string list.
     * 
     * @return string[]|string 
     **/
    public function getCategories();

    /** 
     * Execute this commands.
     * 
     * @return array $arguments The arguments to be passed to the command.
     * @return int The exit code.
     **/
    public function execute(...$arguments): int;
}
