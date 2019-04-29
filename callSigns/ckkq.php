<?php

namespace callSigns;

use model;

class CKKQ implements model\RadioStation {

    private static $API_BASE = "http://ckkq.streamon.fm/hls/metadata/CKKQ-48k.json ";

    private $response; 

    public function __construct() 
    {
        $this->response = json_decode(file_get_contents(self::$API_BASE));
    }

    public function getCurrentSong()
    {
        return $this->response->title;
    }

    public function getCurrentArtist()
    {
        return $this->response->artist;
    }

}

