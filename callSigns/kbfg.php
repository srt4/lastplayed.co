<?php

namespace callSigns;

use model;

class KBFG implements model\RadioStation {

    private $song;
    private $artist;  

    public function __construct() 
    {
        $responseBody = file_get_contents("http://ibz1.com/htdocs/recentlyplayed2.cgi");
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

}
