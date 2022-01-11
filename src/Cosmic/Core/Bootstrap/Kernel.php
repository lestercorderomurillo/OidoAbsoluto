<?php

use Psr\Log\LogLevel;
use Cosmic\Binder\DOM;
use Cosmic\Core\Bootstrap\Application;
use Cosmic\Core\Bootstrap\Logger;
use Cosmic\HTTP\Server\Response;
use Cosmic\HTTP\Server\Session;
use Cosmic\Utilities\Collection;

if (!function_exists('deploy')) {
    /**
     * Deploy a new application into the cosmic runtime. 
     * 
     * @param Application app The application to run.
     * 
     * @return void
     */
    function deploy($app)
    {
        Application::bindApplication($app);
    }
}

if (!function_exists('app')) {
    /**
     * Returns this global binded application instance.
     * If not present, this will return null.
     * 
     * @return Application|null The current application instance.
     */
    function app()
    {
        return Application::getApplication();
    }
}

if (!function_exists('publish')) {
    /**
     * Publish a component into the dependecy container.
     * 
     * @param string[]|string $classNameOrArrayOfClasses The component class to inject into the container. If passed as an array, apply the same to each component.
     * 
     * @return void
     */
    function publish($classNameOrArrayOfClasses)
    {
        $classes = Collection::normalize($classNameOrArrayOfClasses);

        foreach ($classes as $className) {
            app()->get(DOM::class)->registerComponent($className);
        }
    }
}

if (!function_exists('safe')) {
    /**
     * Performs a safe get on this variable. If php fails to get the variable from the direct memory, 
     * this function will return a default value instead of crashing or throwing an exception.
     * 
     * @param mixed &$variable A reference to the variable to try to get from. 
     * @param mixed $default The default value to use. Default is null.
     * 
     * @return mixed|null Can be anything.
     */
    function safe(&$variable, $default = null)
    {
        return (isset($variable) ? $variable : $default);
    }
}

if (!function_exists('log')) {
    /**
     * Logs on the app. This will automatically write on the log file if enabled.
     * 
     * @param string|LogLevel $level PSR Log level of severity. 
     * @param string $message The message to pass. Accepts {number} tokens.
     * @param array $context The context to use to replace {number} tokens with.
     * 
     * @return void
     */
    function log(string $level, string $message, array $context = []): void
    {
        $logger = app()->get(Logger::class);
        $logger->log($level, $message, $context);
    }
}

if (!function_exists('debug')) {
    /**
     * Logs on the app in debug mode. This will automatically write on the log file if enabled.
     * By default, in production mode, this will not log anything out.
     * 
     * @param string $message The message to pass. Accepts {number} tokens message format.
     * @param array $context The context to use to replace {number} tokens with.
     * 
     * @return void
     */
    function debug(string $message, array $context = []): void
    {
        if (!app()->inProductionMode()) {
            log(LogLevel::DEBUG, $message, $context);
        }
    }
}

if (!function_exists('fatal')) {
    /**
     * Creates a new fatal response and sends it to the client. 
     * This method will send the response inmediately completely overriding the default cosmic pipeline behavior.
     * 
     * @param string $message Fatal message to display.
     * 
     * @return void
     */
    function fatal(string $message): void
    {
        $response = new Response();

        $response->setBody("<h4 style='color: red; font-weight: bold;'>$message</h4>");
        $response->setStatusCode(500);
        $response->send();

        exit();
    }
}

if (!function_exists('session')) {
    /**
     * If not passed anything, this method will return the Session object.
     * If a key is present, it will return the value associated with that key.
     * If a key and a value are present, then this method will do an assignment instead of reading,
     * and will return true if the assignment has been performed successfully.
     * 
     * @param string $key The key to retrieve from.
     * @param string $value The value to set to the key, if present.
     * 
     * @return Session|mixed The session object, or a mixed value if necessary.
     */
    function session(string $key = __EMPTY__, string $value = __EMPTY__)
    {
        /**
         * @var Session $session
         **/
        $session = app()->get(Session::class);

        if ($key == __EMPTY__) {
            return $session;
        }

        if ($value == __EMPTY__) {
            return $session->get($key);
        }

        $session->add($key, $value);
        return true;
    }
}

if (!function_exists('configuration')) {
    /**
     * Get the value of a specific configuration key.
     * On error, will throw an exception.
     * 
     * @param string $key The key to retrieve from.
     * 
     * @return string The value of the configuration key.
     * @throws InvalidArgumentException
     */
    function configuration(string $key): string
    {
        return app()->getConfiguration($key);
    }
}

if (!function_exists('generateID')) {
    /**
     * Get a new unique key from php system.
     * 
     * @return string
     */
    function generateID(): string
    {
        return md5(com_create_guid());
    }
}

if (!function_exists('getClassType')) {
    /**
     * Return the string representation of this reflection parameter type.
     * 
     * @param \ReflectionParameter $parameter The reflection parameter to get the type.
     * 
     * @return string|null The type parsed as a string.
     */
    function getClassType($parameter)
    {
        $typeNotParsed = $parameter->getType();

        if ($typeNotParsed instanceof \ReflectionNamedType) {
            return $typeNotParsed->getName();
        }

        return null;
    }
}
