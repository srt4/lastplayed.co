<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KZOK implements model\RadioStation {

    private static $API_BASE = "https://kzok.iheart.com/api/v4/player/live/7787/?sc=inferno&pname=KZOK-FM&theme=light&ihrnetwork=true&embed=true";

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

        // Get the HTML response
        $html = file_get_contents(self::$API_BASE, false, $context);

        // Extract the JSON from the script tag
        if (preg_match('/<script id="initial-props" type="application\/json">(.*?)<\/script>/s', $html, $matches)) {
            $jsonData = json_decode($matches[1]);
            $stationData = $jsonData->initialProps->stationData;
            // stationData is sometimes a JSON-encoded string rather than an object
            if (is_string($stationData)) {
                $stationData = json_decode($stationData);
            }
            $this->response = $stationData;
        } else {
            throw new \Exception("Could not find station data in response");
        }
    }

    public function getCurrentSong()
    {
        // The iHeart API returns the song title in stationData
        return $this->response->title ?? null;
    }

    public function getCurrentArtist()
    {
        // The iHeart API returns the artist in stationData
        return $this->response->artist ?? null;
    }

    public function getRecentSongs()
    {
        // The iHeart embed page only exposes the currently playing song
        return [];
    }

    public function getStationName()
    {
        return "102.5 KZOK";
    }

    public function getStationDescription()
    {
        return "Seattle's Classic Rock Station";
    }

    public function getStationWebsite()
    {
        return "https://kzok.iheart.com";
    }

    public function getStationListenUrl()
    {
        return "https://www.iheart.com/live/1025-kzok-7787/";
    }

    public function getStationLogo()
    {
        return "/logos/kzok.png";
    }

    public function getStationGenre()
    {
        return "Classic Rock";
    }

    public function getStationLocation()
    {
        return "Seattle, WA";
    }

}
