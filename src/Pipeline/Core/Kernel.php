<?php

namespace Pipeline\Kernel;

use Pipeline\Core\Exceptions\UnavailableDependencyException;
use Pipeline\Core\Boot\AppBase;
use Pipeline\HTTP\Server\WebServer;
use Pipeline\HTTP\Server\ServerResponse;
use Pipeline\Trace\Logger;
use Psr\Log\LogLevel;

function app()
{
    return AppBase::$app;
}

function dependency(string $dependency_name)
{
    try {
        return getDependencyInstance($dependency_name);
    } catch (UnavailableDependencyException $e) {
        fatal($e->getMessage());
    }
}

function safeGet(&$variable, $default = null)
{
    return (isset($variable) ? $variable : $default);
}

function log($level, $message, array $context = [])
{
    $logger = dependency(Logger::class);
    $logger->log($level, $message, $context);
}

function debug(string $text, array $parameters = [])
{
    if (!app()->getRuntimeEnvironment()->inProductionMode()) {
        log(LogLevel::DEBUG, $text, $parameters);
    }
}

function fatal(string $message)
{
    ServerResponse::create(500, $message)->sendAndExit();
}

function getDependencyInstance(string $id)
{
    $table = app()->getDependencyTable();
    $dependency = $table->getDependency($id);

    if ($dependency == null) {
        throw new UnavailableDependencyException(
            "Unavailable Dependency Exception [$id]." +
                "<br>Check dependency name classes, uses imports and traits. " +
                "<br>Make sure the dependency has been injected before."
        );
    }

    return $dependency;
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
