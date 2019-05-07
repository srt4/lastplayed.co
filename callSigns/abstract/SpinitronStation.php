<?php

namespace callSigns;

use model;

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

}
