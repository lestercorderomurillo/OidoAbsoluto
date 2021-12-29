<?php

namespace Cosmic\HTTP\Interfaces;

/**
 * This class represents a simple runnable server.
 */
interface ServerInterface
{
    /**
     * Run the server. This server can request dependencies in it's constructor without inconvenients.
     * 
     * @return void
     */
    public function run(): void;
}
