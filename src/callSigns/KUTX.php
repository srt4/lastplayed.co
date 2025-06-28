<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KUTX implements model\RadioStation {

    private static $API_BASE = "https://api.kut.org/v1/broadcasts/kutx/on-air?limit=1";

    private $response; 

    public function __construct() 
    {
        $this->response = json_decode(file_get_contents(self::$API_BASE)); 
    }

    public function getCurrentSong()
    {
        return $this->response->onNow->song->trackName;
    }

    public function getCurrentArtist()
    {
        return $this->response->onNow->song->artistName;
    }

}
