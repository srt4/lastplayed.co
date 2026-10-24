<?php

// Warms the response cache and records play history for every station, so
// visitor requests never wait on an upstream fetch and the history stays
// gapless even when nobody has the site open. Intended to run every minute:
//   * * * * * docker exec apache php /var/www/lastplayed.co/cron/poll.php

// Same temporary autoloader as public/api.php.
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

use LastPlayed\PlayLog;
use LastPlayed\StationFactory;

foreach (StationFactory::callSigns() as $callSign) {
    try {
        $station = StationFactory::create($callSign);
        if (!$station) {
            continue;
        }
        PlayLog::record($callSign, $station);
        echo "$callSign: ok\n";
    } catch (Exception $e) {
        error_log("Poll failed for station '$callSign': " . $e->getMessage());
    }
}
