<?php

namespace callSigns;

use model;

class KZOK implements model\RadioStation {

    private static $API_BASE = "https://us.api.iheart.com/api/v3/live-meta/stream/7787/trackHistory";

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