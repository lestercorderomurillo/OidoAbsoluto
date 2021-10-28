<?php

namespace Pipeline\Navigate;

use Pipeline\App\App;
use Pipeline\HTTP\Server\WebServer;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\Exceptions\UnavailableDependencyException;

function app()
{
    return App::$app;
}

function dependency(string $dependency_name)
{
    try {
        return tryGetDependency($dependency_name);
    } catch (UnavailableDependencyException $e) {
        ServerResponse::create(500, $e->getMessage())->sendAndExit();
    }
}

function debug()
{
}

function log()
{
}

function tryGetDependency(string $dependency_name)
{
    $container = app()->getDependencyManager()->getContainer();

    if ($container->has($dependency_name)) {
        return $container->get($dependency_name);
    } else {
        throw new UnavailableDependencyException(
            "Unavailable Dependency Exception [$dependency_name].
            Check dependency name classes, uses imports and traits. 
            Make sure the dependency has been injected before."
        );
    }
}

function session(string $key = "", string $value = "")
{
    $session = dependency(WebServer::class)->getActiveSession();

    if ($key == "") {
        return $session;
    }

    if ($value == "") {
        return $session->get($key);
    }

    $session->store($key, $value);
    return true;
}

function configuration(string $key)
{
    return app()->getRuntimeEnvironment()->getConfiguration($key);
}

function GUID()
{
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }

    return sprintf(
        '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
        mt_rand(0, 65535),
        mt_rand(0, 65535),
        mt_rand(0, 65535),
        mt_rand(16384, 20479),
        mt_rand(32768, 49151),
        mt_rand(0, 65535),
        mt_rand(0, 65535),
        mt_rand(0, 65535)
    );
}
