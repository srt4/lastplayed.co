<?php

// This is a temporary autoloader. Ideally, you would run `composer install`
// to generate a proper one.
spl_autoload_register(function ($class) {
    $prefix = 'LastPlayed\\';
    $base_dir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

$callSign = $_GET['call_sign'] ?? '';

// Redirect to the frontend with the call sign
header("Location: /public/index.html#/$callSign");
exit; 