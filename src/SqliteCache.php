<?php

namespace LastPlayed;

use PDO;
use PDOException;

// Caches upstream station responses in SQLite so the source is hit at most
// once per TTL window no matter how many visitors are polling. When the
// upstream fails, the last good response is served instead of an error.
final class SqliteCache
{
    /**
     * Returns the response body for $url, fetching it live at most once
     * per $ttl seconds. $context is an optional stream context passed to
     * the underlying file_get_contents call.
     *
     * @throws \RuntimeException when the upstream cannot be reached and
     *         nothing has ever been cached for this URL.
     */
    public static function fetch(string $url, int $ttl, $context = null): string
    {
        $pdo = Database::connection();
        $key = hash('sha256', $url);
        $now = time();

        $stmt = $pdo->prepare('SELECT body, fetched_at FROM cache WHERE key = :key');
        $stmt->execute([':key' => $key]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $now - (int) $row['fetched_at'] < $ttl) {
            return $row['body'];
        }

        if ($row ? self::claim($pdo, $key, (int) $row['fetched_at'], $now)
                 : self::claimNew($pdo, $key, $now)) {
            $body = @file_get_contents($url, false, $context);
            if (is_string($body) && $body !== '') {
                $pdo->prepare('UPDATE cache SET body = :body, fetched_at = :now WHERE key = :key')
                    ->execute([':body' => $body, ':now' => $now, ':key' => $key]);
                return $body;
            }
            // The upstream failed. The claim stands so retries stay limited
            // to one per TTL window; serve the previous body if there is one.
            if ($row && $row['body'] !== '') {
                return $row['body'];
            }
            $pdo->prepare("DELETE FROM cache WHERE key = :key AND body = ''")
                ->execute([':key' => $key]);
            throw new \RuntimeException("Could not fetch $url and no cached copy exists.");
        }

        // Another request won the refresh race; serve the stale copy.
        if ($row && $row['body'] !== '') {
            return $row['body'];
        }

        // Cold start while another request is mid-fetch: fetch directly
        // rather than fail, but leave storing to the claim holder.
        $body = @file_get_contents($url, false, $context);
        if (!is_string($body) || $body === '') {
            throw new \RuntimeException("Could not fetch $url.");
        }
        return $body;
    }

    // Atomically marks a stale row as being refreshed; only the single
    // request that wins the race gets true.
    private static function claim(PDO $pdo, string $key, int $previousFetch, int $now): bool
    {
        $stmt = $pdo->prepare('UPDATE cache SET fetched_at = :now WHERE key = :key AND fetched_at = :previous');
        $stmt->execute([':now' => $now, ':key' => $key, ':previous' => $previousFetch]);
        return $stmt->rowCount() === 1;
    }

    private static function claimNew(PDO $pdo, string $key, int $now): bool
    {
        try {
            $pdo->prepare("INSERT INTO cache (key, body, fetched_at) VALUES (:key, '', :now)")
                ->execute([':key' => $key, ':now' => $now]);
            return true;
        } catch (PDOException $e) {
            return false; // Another request inserted the placeholder first.
        }
    }
}
