<?php

namespace callSigns;

use model;

class KMGP implements model\RadioStation {

    private static $BASE_URL = "https://widgets.spinitron.com/KMGP/";

    private $song;
    private $artist;  

    public function __construct() 
    {
        $responseBody = file_get_contents("https://widgets.spinitron.com/KMGP/");
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


}
