<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3144095f9327109f403a002d24f5c5f9
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'ScssPhp\\ScssPhp\\' => 16,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'Psr\\Http\\Server\\' => 16,
            'Psr\\Http\\Message\\' => 17,
            'Psr\\Http\\Client\\' => 16,
            'Psr\\Container\\' => 14,
            'Pipeline\\' => 9,
        ),
        'M' => 
        array (
            'Mimey\\' => 6,
        ),
        'A' => 
        array (
            'App\\Models\\' => 11,
            'App\\Middlewares\\' => 16,
            'App\\Controllers\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ScssPhp\\ScssPhp\\' => 
        array (
            0 => __DIR__ . '/..' . '/scssphp/scssphp/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Psr\\Http\\Server\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-server-handler/src',
            1 => __DIR__ . '/..' . '/psr/http-server-middleware/src',
        ),
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
        ),
        'Psr\\Http\\Client\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-client/src',
        ),
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'Pipeline\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/Pipeline',
        ),
        'Mimey\\' => 
        array (
            0 => __DIR__ . '/..' . '/ralouphie/mimey/src',
        ),
        'App\\Models\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/models',
        ),
        'App\\Middlewares\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/middlewares',
        ),
        'App\\Controllers\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/controllers',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3144095f9327109f403a002d24f5c5f9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3144095f9327109f403a002d24f5c5f9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3144095f9327109f403a002d24f5c5f9::$classMap;

        }, null, ClassLoader::class);
    }
}
