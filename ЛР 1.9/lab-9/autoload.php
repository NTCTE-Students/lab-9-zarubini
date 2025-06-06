<?php

spl_autoload_register(function ($class) {
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    $full_path = __DIR__ . "/{$path}.php";

    if (file_exists($full_path)) {
        require_once $full_path;
    }
});