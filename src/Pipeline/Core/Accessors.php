<?php

namespace Pipeline\Accessors;

use Pipeline\App\App;
use Pipeline\HTTP\Server\WebServer;
use Pipeline\DependencyInjection\Exceptions\UnavailableDependencyException;

function App()
{
    return App::$app;
}

function Dependency(string $dependency_name)
{
    $container = App()->getDependencyManager()->getContainer();

    if ($container->has($dependency_name)) {
        return $container->get($dependency_name);
    } else {
        throw new UnavailableDependencyException("Unavailable Dependency [$dependency_name]. Check dependency names, classes and traits.");
    }
}

function Session(string $key = "", string $value = "")
{
    $session = Dependency(WebServer::class)->getSession();

    if ($key == "") {
        return $session;
    }

    if ($value == "") {
        return $session->get($key);
    }

    $session->store($key, $value);
    return true;
}

function Configuration(string $key)
{
    return App()->getRuntimeEnvironment()->getConfiguration($key);
}
