<?php

namespace callSigns;

use model;

class KUTX implements model\RadioStation {

    private static $API_BASE = "https://api.composer.nprstations.org/v1/widget/50ef24ebe1c8a1369593d032/now?format=json";

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
