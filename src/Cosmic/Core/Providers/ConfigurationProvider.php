<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\Core\Providers;

use Cosmic\Utilities\Collections;
use Cosmic\Core\Abstracts\AutoProvider;
use Cosmic\Core\Interfaces\ReadOnlyContainerInterface;

class ConfigurationProvider extends AutoProvider implements ReadOnlyContainerInterface
{
    protected $configuration;

    /**
     * @inheritdoc
     */
    public static function boot(): void
    {
        self::instance()->configuration = Collections::from("app\Configuration.json"); 
    }

    /**
     * @inheritdoc
     */
    public static function provide()
    {
        cout("ConfigurationProvider has finished.");
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return $this->configuration[$key];
    }

    /**
     * @inheritdoc
     */
    public function all(): array
    {
        return $this->configuration;
    }

    /**
     * @inheritdoc
     */
    public function has($key): bool
    {
        return isset($this->configuration[$key]);
    }
}
