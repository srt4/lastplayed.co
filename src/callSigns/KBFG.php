<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;
use LastPlayed\SqliteCache;

class KBFG extends base\SpinitronStation {

    private $song;
    private $artist;  

    public function __construct() 
    {
        $responseBody = SqliteCache::fetch("http://ibz1.com/htdocs/recentlyplayed2.cgi", 60);
        $songArtist = array(); // artist is index 2, song is index 1 
        preg_match("/<b>(.+?)<\/b><br>By: (.+?)<br>/", $responseBody, $songArtist);
        $this->artist = $songArtist[2]; 
        $this->song = $songArtist[1]; 
    }

    public function getCurrentSong()
    {
        return $this->song;
    }

    public function getCurrentArtist()
    {
        return $this->artist;
    }

    protected function getCallSign() 
    {
        return "KBFG"; 
    } 

    public function getStationName() { return "KBFG 107.3"; }
    public function getStationDescription() { return "Volunteer-run community radio for northwest Seattle"; }
    public function getStationWebsite() { return "https://kbfgseattle.org"; }
    public function getStationListenUrl() { return "https://kbfgseattle.org/how-to-listen/"; }
    public function getStationLogo() { return "/logos/kbfg.jpg"; }
    public function getStationGenre() { return "Local music and public affairs"; }
    public function getStationLocation() { return "Seattle, WA"; }

}
