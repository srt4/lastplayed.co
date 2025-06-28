<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KZOK implements model\RadioStation {

    private static $API_BASE = "https://live.amperwave.net/playlist/KZOK-FM-AAC";

    private $response; 

    public function __construct() 
    {
        $this->response = json_decode(file_get_contents(self::$API_BASE)); 
    }

    public function getCurrentSong()
    {
        return $this->response->data[0]->title;
    }

    public function getCurrentArtist()
    {
        return $this->response->data[0]->artist;
    }

}