<?php

namespace LastPlayed;

use LastPlayed\model\RadioStation;
use PDO;

// Server-side play history. Records what each station plays into SQLite so
// the log survives across browsers and devices, and so the recent endpoint
// can serve more than the handful of tracks the upstreams expose.
final class PlayLog
{
    // Mirrors the 15-minute window the frontend uses when deciding whether
    // two timestamps describe the same play.
    private const MERGE_WINDOW_SECONDS = 900;

    public static function record(string $callSign, RadioStation $station): void
    {
        $callSign = strtolower(trim($callSign));
        $pdo = Database::connection();

        $insert = $pdo->prepare('INSERT OR IGNORE INTO plays (station, artist, song, played_at, source)
                                 VALUES (:station, :artist, :song, :played_at, :source)');

        // Station-reported times are more accurate than our observed ones,
        // so drop any observed row that describes the same play.
        $upgrade = $pdo->prepare("DELETE FROM plays WHERE station = :station AND artist = :artist
                                  AND song = :song AND source = 'observed'
                                  AND ABS(played_at - :played_at) < " . self::MERGE_WINDOW_SECONDS);

        foreach ($station->getRecentSongs() as $entry) {
            $song = trim($entry['song'] ?? '');
            $artist = trim($entry['artist'] ?? '');
            $playedAt = isset($entry['timestamp']) ? strtotime($entry['timestamp']) : null;
            if ($song === '' || $artist === '' || !$playedAt) {
                continue; // Plays without a usable time can't be deduplicated.
            }
            $params = [
                ':station' => $callSign,
                ':artist' => $artist,
                ':song' => $song,
                ':played_at' => $playedAt,
            ];
            $upgrade->execute($params);
            $insert->execute($params + [':source' => $entry['timestampSource'] ?? 'station']);
        }

        self::recordCurrent($pdo, $callSign, $station);
    }

    /**
     * Most recent plays for a station, newest first. Each entry matches the
     * shape the station adapters return, plus a timestampSource.
     */
    public static function recent(string $callSign, int $limit = 50): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT artist, song, played_at, source FROM plays
             WHERE station = :station ORDER BY played_at DESC, id DESC LIMIT :limit'
        );
        $stmt->bindValue(':station', strtolower(trim($callSign)));
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(function ($row) {
            return [
                'song' => $row['song'],
                'artist' => $row['artist'],
                'timestamp' => gmdate(\DateTime::ATOM, (int) $row['played_at']),
                'timestampSource' => $row['source'],
            ];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // The currently playing song carries no upstream timestamp, so log it
    // with the time we saw it — but only when it differs from the latest
    // recorded play, keeping repeat polls idempotent.
    private static function recordCurrent(PDO $pdo, string $callSign, RadioStation $station): void
    {
        $song = trim((string) $station->getCurrentSong());
        $artist = trim((string) $station->getCurrentArtist());
        if ($song === '' || $artist === '') {
            return;
        }

        $latest = $pdo->prepare('SELECT artist, song FROM plays WHERE station = :station
                                 ORDER BY played_at DESC, id DESC LIMIT 1');
        $latest->execute([':station' => $callSign]);
        $row = $latest->fetch(PDO::FETCH_ASSOC);
        if ($row && $row['artist'] === $artist && $row['song'] === $song) {
            return;
        }

        $pdo->prepare("INSERT OR IGNORE INTO plays (station, artist, song, played_at, source)
                       VALUES (:station, :artist, :song, :played_at, 'observed')")
            ->execute([
                ':station' => $callSign,
                ':artist' => $artist,
                ':song' => $song,
                ':played_at' => time(),
            ]);
    }
}
