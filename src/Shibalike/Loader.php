<?php

namespace Shibalike;

class Loader {
    public static $libPath;

    public static function load($className)
    {
        $className = ltrim($className, '\\');
        if (0 === strpos($className, 'Shibalike\\')) {
            $file = self::$libPath . DIRECTORY_SEPARATOR . str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $className) . '.php';
            if (is_readable($file)) {
                require $file;
            }
        }
    }

    public static function register()
    {
        self::$libPath = dirname(__DIR__);
        spl_autoload_register(array('Shibalike\\Loader', 'load'));
    }
}