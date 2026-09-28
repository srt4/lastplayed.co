<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KNDD implements model\RadioStation {

    private static $API_BASE = "https://api-nowplaying.amperwave.net/prt/nowplaying/2/5/7028/nowplaying.json";

    private $response; 

    public function __construct() 
    {
        // Set Chrome user agent header
        $options = [
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
            ]
        ];
        $context = stream_context_create($options);

        // Make the request using the modified context
        $this->response = json_decode(file_get_contents(self::$API_BASE, false, $context));
    }

    public function getCurrentSong()
    {
        // Get the first (most recent) performance from the list
        return $this->response->performances[0]->title;
    }

    public function getCurrentArtist()
    {
        // Get the first (most recent) performance from the list
        return $this->response->performances[0]->artist;
    }

    public function getRecentSongs()
    {
        $recent = [];
        foreach (array_slice($this->response->performances ?? [], 1) as $performance) {
            if (empty($performance->title) || empty($performance->artist)) {
                continue;
            }
            $timestamp = null;
            if (!empty($performance->time)) {
                // e.g. "2026-08-09T06:57:40+0000" -> ISO 8601
                $timestamp = preg_replace('/([+-]\d{2})(\d{2})$/', '$1:$2', $performance->time);
            }
            $recent[] = [
                'song' => $performance->title,
                'artist' => $performance->artist,
                'timestamp' => $timestamp,
            ];
        }
        return $recent;
    }

    public function getStationName()
    {
        return "107.7 The End";
    }
    
    public function getStationDescription()
    {
        return "Seattle's Alternative Rock Station";
    }
    
    public function getStationWebsite()
    {
        return "https://www.audacy.com/1077theend";
    }

    public function getStationListenUrl()
    {
        return "https://www.audacy.com/stations/1077theend";
    }
    
    public function getStationLogo()
    {
        return "/logos/kndd.jpg";
    }
    
    public function getStationGenre()
    {
        return "Alternative Rock";
    }
    
    public function getStationLocation()
    {
        return "Seattle, WA";
    }

}
