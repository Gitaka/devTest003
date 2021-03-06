<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9caf516ea52f141b53595971dfbcdb00
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stomp\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stomp\\' => 
        array (
            0 => __DIR__ . '/..' . '/stomp-php/stomp-php/src/Stomp',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9caf516ea52f141b53595971dfbcdb00::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9caf516ea52f141b53595971dfbcdb00::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
