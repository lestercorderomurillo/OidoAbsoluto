<?php

namespace Cosmic\Core\Boot;

require_once("Kernel.php");

/**
 * This class represents a cosmic application.
 */
abstract class Application extends Environment
{
    /**
     * @var Application $app The running application instance.
     */
    private static $app = null;

    /**
     * This method should prepare the application before injecting any dependencies.
     * 
     * @return void
     */
    protected abstract function onConfiguration(): void;

    /**
     * This method should inject all dependencies using the configuration provided in the onConfiguration() method.
     * 
     * @return void
     */
    protected abstract function onServicesInjection(): void;

    /**
     * This method should execute the application pipeline. 
     * Usually this method will call the WebServer dependency on the end.
     * 
     * @return void
     */
    protected abstract function onInitialization(): void;


    /**
     * Builds the app. Defines new root constants in the runtime.
     * 
     * @return void
     * 
     */
    public function buildApplication(): void
    {
        $this->inject(Lifetime::RequestLifetime, Logger::class);
        $this->createHostEnvironment();
        $this->setupServerScheme();
        $this->setupExceptionHandlers();

        /* For constants in compile time */
        define("__ROOT__", $this->getRootDirectoryString());
        define("__CONTENT__", $this->getContentDirectoryString());
        define("__HOST__", $this->getHostString());
        define("__EMPTY__", "");
    }

    /**
     * Get the CWD(current working directory) of this Cosmic application.
     * 
     * @return string
     */
    public function getRootDirectoryString(): string
    {
        return str_replace("\\", DIRECTORY_SEPARATOR, dirname(__DIR__, 4) . "\\");
    }

    /**
     * Get the content folder of this Cosmic application.
     * 
     * @return string
     */
    public function getContentDirectoryString(): string
    {
        return $this->getRootDirectoryString() . "app\\" . $this->getConfiguration("application.content")  . "\\";
    }

    /**
     * Get the website host URL.
     * 
     * @return string
     */
    public function getHostString(): string
    {
        return $this->getURLScheme() . "://" . $this->getConfiguration("application.url");
    }

    /**
     * Binds a new application to the engine. If success, the engine will execute 
     * the application pipeline until the a response is returned or and error occurs.
     * 
     * Can only bind one application at a time, if not, will throw an exception.
     * 
     * @param Application $app The application to bind.
     * 
     * @return void
     */
    public static function bind(Application $app): void
    {
        if (self::$app == null) {
            self::$app = $app;
            $app->buildApplication();
            $app->onConfiguration();
            $app->onServicesInjection();
            $app->onInitialization();
        } else {
            throw new \RuntimeException("An application is already initialized.");
        }
    }

    /**
     * Returns this current application instance.
     * If not present, this method will return null.
     * 
     * @return Application|null
     */
    public static function getApplication()
    {
        return self::$app;
    }
}
