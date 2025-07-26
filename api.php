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

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // For local development

$callSign = $_GET['call_sign'] ?? '';
$station = LastPlayed\StationFactory::create($callSign);

if (!$station) {
    http_response_code(404);
    echo json_encode(['error' => "Station '$callSign' not found."]);
    exit;
}

try {
    $song = $station->getCurrentSong();
    $artist = $station->getCurrentArtist();

    echo json_encode([
        'callSign' => strtoupper($callSign),
        'song' => $song,
        'artist' => $artist,
        'spotifySearchUrl' => 'https://play.spotify.com/search/' . urlencode("$artist $song"),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => "Failed to fetch data for station '$callSign'. Please try again later."]);
} 