<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

use Psr\Log\LogLevel;
use Cosmic\Core\Abstracts\Application;
use Cosmic\Core\Exceptions\NotFoundDependencyException;
use Cosmic\Core\Providers\ConfigurationProvider;

$stdoutQueue = [];

if (!function_exists('__SYSCALL__')) {
    /**
     * Deploy a new application into the cosmic runtime.
     * This method should be called only once. 
     * 
     * @param string $className The application to run.
     * @return void
     */
    function __SYSCALL__(string $className, $args = [])
    {
        Application::bind(new $className(...$args));
    }
}

if (!function_exists('app')) {
    /**
     * Returns this global binded application instance.
     * If not present, this will return null.
     * 
     * 
     */
    function app($alias = '')
    {
        return Application::app($alias);
    }
}

if (!function_exists('create')) {
    /**
     */
    function create($alias, ...$arguments)
    {
        return app()->create($alias, ...$arguments);
    }

    function __($alias, ...$arguments)
    {
        return create($alias, ...$arguments);
    }
}

if (!function_exists('command')) {
    /**
     */
    function command($alias)
    {
        $hphpCommand = create($alias);
        return $hphpCommand->execute();
    }
}




if (!function_exists('publish')) {
    /**
     * Publish a component into the IoC container.
     * 
     * @param string[]|string $classNameOrArrayOfClasses The component class to inject into the container. 
     * If passed as an array, apply the same to each component.
     * @return void
     */
    /*function publish($classNameOrArrayOfClasses)
    {
        $classes = Collections::normalizeToList($classNameOrArrayOfClasses);

        foreach ($classes as $className) {
            app()->get(Bindings::class)->registerComponent($className);
        }
    }*/
}

if (!function_exists('tryGet')) {
    /**
     * Performs a tryGet get on this variable. If php fails to get the variable from the direct memory, 
     * this function will return a default value instead of crashing or throwing an exception.
     * 
     * @param mixed $variable A reference to the variable to try to get from. 
     * @param mixed $default The default value to use. Default is null.
     * @return mixed|null Can be anything.
     */
    function tryGet(&$variable, $default = null)
    {
        return isset($variable) ? $variable : $default;
    }
}

if (!function_exists('cout')) {
    /**
     * Logs on the app. This will automatically write on the log file if enabled.
     * 
     * @param string $message The message to pass. Accepts {number} tokens.
     * @param array $context The context to use to replace {number} tokens with.
     * @param string|LogLevel $level PSR Log level of severity. 
     * @return void
     */
    function cout($message, $context = [], $level = LogLevel::DEBUG): void
    {
        try {

            if (__CONSOLE__) {
                $logger = app('ConsoleLogger');
            } else {
                $logger = app('Logger');
            }
        } catch (NotFoundDependencyException $e) {

            $logger = null;
        }

        $GLOBALS["stdoutQueue"][]  = [$level, $message, $context];

        if ($logger != null) {

            foreach ($GLOBALS["stdoutQueue"] as $out) {
                $logger->log($out[0], $out[1], $out[2]);
            }

            $GLOBALS["stdoutQueue"] = [];
        }
    }
}

if (!function_exists('fatal')) {
    /**
     * Creates a new fatal response and sends it to the client. 
     * This method will send the response inmediately completely overriding the default cosmic behavior.
     * 
     * @param string $message Fatal message to display.
     * @return void
     */
    /*function fatal(string $message): void
    {
        $response = new Response();

        $response->setBody("<h4 style='color: red; font-weight: bold;'>$message</h4>");
        $response->setStatusCode(500);
        $response->send();

        debug(LogLevel::EMERGENCY, $message);
        exit();
    }*/
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
     * @return Session|mixed The session object, or a mixed value if necessary.
     */
    /*function session(string $key = __EMPTY__, string $value = __EMPTY__)
    {
        /**
         * @var Session $session
         *
        $session = app()->get(Session::class);

        if ($key == __EMPTY__) {
            return $session;
        }

        if ($value == __EMPTY__) {
            return $session->get($key);
        }

        $session->add($key, $value);
        return true;
    }*/
}

if (!function_exists('configuration')) {
    /**
     * Get the value of a specific configuration key.
     * On error, will throw an exception.
     * 
     * @param string $key The key to retrieve from.
     * @return mixed The value of the configuration key.
     * @throws InvalidArgumentException
     */
    function configuration(string $key)
    {
        return ConfigurationProvider::instance()->get($key);
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

if (!function_exists('getNativeType')) {
    /**
     * Return the string representation of this reflection parameter type.
     * 
     * @param \ReflectionParameter $parameter The reflection parameter to get the type.
     * @return string|null The type parsed as a string.
     */
    function getNativeType($parameter)
    {
        $typeNotParsed = $parameter->getType();

        if ($typeNotParsed instanceof \ReflectionNamedType) {
            return $typeNotParsed->getName();
        }

        return null;
    }
}

if (!function_exists('str_starts_with')) {
    /**
     * Checks if the given string starts with another one.
     * The search will start at the offset position.
     * 
     * @param string $text The input string.
     * @param string $check The string to search.
     * @return bool True if it does, false otherwise.
     */
    function str_starts_with(string $text, string $check): bool
    {
        return substr($text, 0, strlen($check)) === $check;
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * Checks if the given string ends with another one.
     * 
     * @param string $text The input string.
     * @param string $check The string to search.
     * @return bool True if it does, false otherwise.
     */
    function str_ends_with(string $text, string $check): bool
    {
        $length = strlen($check);
        if (!$length) {
            return true;
        }
        return substr($text, -$length) === $check;
    }
}
