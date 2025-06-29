<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KNDD implements model\RadioStation {

    private static $API_BASE = "https://api-nowplaying.amperwave.net/prt/nowplaying/2/5/7028/nowplaying.json";

    private $response; 

    public function __construct() 
    {
        $this->response = json_decode(file_get_contents(self::$API_BASE)); 
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

}