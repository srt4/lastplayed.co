<?php

namespace callSigns;

use model;

class KNDD implements model\RadioStation {

    private static $API_BASE = "https://1077theend.radio.com/get.php?callback=_freq_tagstation_data&type=current&count=false";

    private $response; 

    public function __construct() 
    {
        $this->response = json_decode(file_get_contents(self::$API_BASE)); 
    }

    public function getCurrentSong()
    {
        return $this->response->currentEvent->title;
    }

    public function getCurrentArtist()
    {
        return $this->response->currentEvent->artist;
    }

}