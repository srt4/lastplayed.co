<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;
use LastPlayed\SqliteCache;

class KUTX implements model\RadioStation {

    // The /tracks endpoint returns both onNow and the recent tracklist
    private static $API_BASE = "https://api.composer.nprstations.org/v1/widget/50ef24ebe1c8a1369593d032/tracks?format=json";

    private $response; 

    public function __construct() 
    {
        // Set Chrome user agent header for consistency
        $options = [
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
            ]
        ];
        $context = stream_context_create($options);

        // Make the request using the modified context
        $this->response = json_decode(SqliteCache::fetch(self::$API_BASE, 15, $context));
    }

    public function getCurrentSong()
    {
        return $this->response->onNow->song->trackName;
    }

    public function getCurrentArtist()
    {
        return $this->response->onNow->song->artistName;
    }

    public function getRecentSongs()
    {
        $recent = [];
        $timezone = new \DateTimeZone('America/Chicago');
        foreach ($this->response->tracklist->results ?? [] as $result) {
            $song = $result->song->trackName ?? null;
            $artist = $result->song->artistName ?? null;
            if (!$song || !$artist) {
                continue;
            }
            $timestamp = null;
            if (!empty($result->song_start_time)) {
                // e.g. "08-09-2026 01:54:46", station-local time
                $date = \DateTime::createFromFormat('m-d-Y H:i:s', $result->song_start_time, $timezone);
                if ($date) {
                    $timestamp = $date->format(\DateTime::ATOM);
                }
            }
            $recent[] = [
                'song' => $song,
                'artist' => $artist,
                'timestamp' => $timestamp,
            ];
        }
        return $recent;
    }

    public function getStationName()
    {
        return "KUTX 98.9";
    }
    
    public function getStationDescription()
    {
        return "Austin's Music Discovery - The University of Texas at Austin Radio";
    }
    
    public function getStationWebsite()
    {
        return "https://kutx.org";
    }

    public function getStationListenUrl()
    {
        return "https://kutx.org/streams/";
    }
    
    public function getStationLogo()
    {
        return "/logos/kutx.svg";
    }
    
    public function getStationGenre()
    {
        return "Eclectic, Indie, Local Music";
    }
    
    public function getStationLocation()
    {
        return "Austin, TX";
    }

}
