<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KEXP implements model\RadioStation {

    private static $API_BASE = "https://legacy-api.kexp.org/play/?limit=1&ordering=-airdate&offset=0";

    private $response; 

    public function __construct() 
    {
        $this->response = json_decode(file_get_contents(self::$API_BASE));
    }

    public function getCurrentSong()
    {
        return $this->response->results[0]->track->name;
    }

    public function getCurrentArtist()
    {
        return $this->response->results[0]->artist->name;
    }

}
