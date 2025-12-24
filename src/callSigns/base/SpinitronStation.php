<?php

namespace LastPlayed\callSigns\base;

use LastPlayed\model;

abstract class SpinitronStation implements model\RadioStation {

    private static $BASE_URL = "https://widgets.spinitron.com/";

    private $song;
    private $artist;  

    public function __construct() 
    {
        $responseBody = file_get_contents("https://widgets.spinitron.com/" 
            . strtoupper($this->getCallSign()) . "/");
        $songArtist = array(); // artist is index 1, song is index 2 
        preg_match("/class=\"artist\">(.+?)<.+?class=\"song\">(.+?)</", $responseBody, $songArtist);
        $this->artist = $songArtist[1]; 
        $this->song = $songArtist[2]; 
    }

    public function getCurrentSong()
    {
        return $this->song;
    }

    public function getCurrentArtist()
    {
        return $this->artist;
    }

    protected abstract function getCallSign();
    
    // Default implementations for metadata - can be overridden by subclasses
    public function getStationName()
    {
        return strtoupper($this->getCallSign());
    }
    
    public function getStationDescription()
    {
        return "Community radio station " . $this->getStationName();
    }
    
    public function getStationWebsite()
    {
        return "https://" . strtolower($this->getCallSign()) . ".org";
    }
    
    public function getStationLogo()
    {
        return "/logos/" . strtolower($this->getCallSign()) . ".png";
    }
    
    public function getStationGenre()
    {
        return "Variety";
    }
    
    public function getStationLocation()
    {
        return "Unknown Location";
    }

}
