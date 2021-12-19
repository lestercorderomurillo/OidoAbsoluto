<?php

namespace Cosmic\Core\Boot;

/**
 * This class is used to represent different kinds of lifecycles for dependencies.
 */
class Lifetime
{
    /**
     * Server will create a new instance when it does not exist. 
     * The container will serialize the instance inside a file. 
     * When requested, the instance is rebuilded if it doesn't exists.
     * This is the most persistent lifetime a available.
     */
    const SerializableLifetime = 1;

    /**
     * The instance is created when the dependency is injected into the container.
     * When requested, this scope will return the stored instance.
     * This is the usual scoped used when injecting dependencies.
     */
    const RequestLifetime = 2;

    /**
     * New instance will be generated each time a dependency is requested.
     * The container will only store the parameters required to create the instance.
     * This is the most volatile lifetime available.
     */
    const ContextLifetime = 3;
}