<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit53c5d4bf992da14aa82b112745555d42
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Sxy\\LpcnGpMt\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Sxy\\LpcnGpMt\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'S' => 
        array (
            'StanfordNLP' => 
            array (
                0 => __DIR__ . '/..' . '/agentile/php-stanford-nlp/src',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit53c5d4bf992da14aa82b112745555d42::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit53c5d4bf992da14aa82b112745555d42::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit53c5d4bf992da14aa82b112745555d42::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit53c5d4bf992da14aa82b112745555d42::$classMap;

        }, null, ClassLoader::class);
    }
}
