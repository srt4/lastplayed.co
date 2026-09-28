<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KEXP implements model\RadioStation {

    private static $API_BASE = "https://legacy-api.kexp.org/play/?limit=11&ordering=-airdate&offset=0";

    private $response; 

    public function __construct() 
    {
        $this->response = json_decode(file_get_contents(self::$API_BASE));
    }

    public function getCurrentSong()
    {
        return $this->response->results[0]->track->name ?? null;
    }

    public function getCurrentArtist()
    {
        return $this->response->results[0]->artist->name ?? null;
    }

    public function getRecentSongs()
    {
        $recent = [];
        foreach (array_slice($this->response->results ?? [], 1) as $play) {
            $song = $play->track->name ?? null;
            $artist = $play->artist->name ?? null;
            if (!$song || !$artist) {
                continue; // Skip air breaks and non-music plays
            }
            $recent[] = [
                'song' => $song,
                'artist' => $artist,
                'timestamp' => $play->airdate ?? null,
            ];
        }
        return $recent;
    }

    public function getStationName()
    {
        return "KEXP";
    }
    
    public function getStationDescription()
    {
        return "Where the Music Matters - Seattle's independent music discovery station";
    }
    
    public function getStationWebsite()
    {
        return "https://kexp.org";
    }

    public function getStationListenUrl()
    {
        return "https://www.kexp.org/playlist/";
    }
    
    public function getStationLogo()
    {
        return "/logos/kexp.svg";
    }
    
    public function getStationGenre()
    {
        return "Alternative, Indie, World Music";
    }
    
    public function getStationLocation()
    {
        return "Seattle, WA";
    }

}
