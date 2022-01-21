<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, HPHP for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\HPHP\Providers;

use Cosmic\Core\Interfaces\ProviderInterface;
use Cosmic\FileSystem\Paths\FilePath;

/**
 * This class represents
 */
class CSSPropertyNamesProvider implements ProviderInterface
{
    private static $props;
    /**
     * @inheritdoc
     */
    public static function boot(): void
    {
        static::$props = [];
        $cssPropsFile = new \SplFileObject(new FilePath("src/Cosmic/HPHP/Providers/CSSProperties.txt"));

        while (!$cssPropsFile->eof()) {
            static::$props[] = $cssPropsFile->fgets();
        }

        $cssPropsFile = null;
    }

    /**
     * @return mixed
     */
    public static function provide()
    {
        return static::$props;
    }
}
