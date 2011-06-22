<?php

set_include_path(realpath(__DIR__ . '/../src') . PATH_SEPARATOR  . get_include_path());

function shibalikeExamples_autoload($className) {
    $className = ltrim($className, '\\');
    $path = str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $className) . '.php';
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $includePath) {
        if (is_readable("$includePath/$path")) {
            require "$includePath/$path";
            return;
        }
    }
}

spl_autoload_register('shibalikeExamples_autoload');
