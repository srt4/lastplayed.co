<?php

namespace LastPlayed;

use PDO;

// Single shared SQLite connection. The database file lives outside the
// web-served directories in data/ and backs both the upstream response
// cache (SqliteCache) and the server-side play history (PlayLog).
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dir = dirname(__DIR__) . '/data';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $pdo = new PDO('sqlite:' . $dir . '/lastplayed.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $pdo->exec('CREATE TABLE IF NOT EXISTS cache (
            key TEXT PRIMARY KEY,
            body TEXT NOT NULL,
            fetched_at INTEGER NOT NULL
        )');
        $pdo->exec('CREATE TABLE IF NOT EXISTS plays (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            station TEXT NOT NULL,
            artist TEXT NOT NULL,
            song TEXT NOT NULL,
            played_at INTEGER NOT NULL,
            source TEXT NOT NULL,
            UNIQUE (station, artist, song, played_at)
        )');
        $pdo->exec('CREATE INDEX IF NOT EXISTS plays_station_time ON plays (station, played_at DESC)');

        return self::$pdo = $pdo;
    }
}
