<?php

// This is a temporary autoloader. Ideally, you would run `composer install`
// to generate a proper one.
require __DIR__ . '/../vendor/autoload.php';

use React\EventLoop\Factory;

$loop = Factory::create();

$autoloadAsync = function ($class) use ($loop) {
    $prefix = 'LastPlayed\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    $loop->futureTick(function () use ($file) {
        if (file_exists($file)) {
            require $file;
        }
    });
};

spl_autoload_register($autoloadAsync);

$callSign = $_GET['call_sign'] ?? '';

// Redirect to the frontend with the call sign
header("Location: /public/index.html#/$callSign");
exit;
