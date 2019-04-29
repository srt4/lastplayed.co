<?php

namespace callSigns;

use model;

class KEXP implements model\RadioStation {

    private static $BASE_URL = "https://widgets.spinitron.com/KXSU/";

    private $song;
    private $artist;  

    public function __construct() 
    {
        $responseBody = file_get_contents("https://widgets.spinitron.com/KXSU/");
        $songArtist = array(); // artist is index 1, song is index 2 
        preg_match("/class=\"artist\">(.+?)<.+?class=\"song\">(.+?)</", $responseBody, $songArtist);
        self::$artist = $songArtist[1]; 
        self::$song = $songArtist[2]; 
    }

    public function getCurrentSong()
    {
        return self::$song;
    }

    public function getCurrentArtist()
    {
        return self::$artist;
    }


}