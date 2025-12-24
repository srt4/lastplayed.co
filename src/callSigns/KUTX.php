<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KUTX implements model\RadioStation {

    private static $API_BASE = "https://api.composer.nprstations.org/v1/widget/50ef24ebe1c8a1369593d032/now?format=json";

    private $response; 

    public function __construct() 
    {
        // Set Chrome user agent header for consistency
        $options = [
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
            ]
        ];
        $context = stream_context_create($options);

        // Make the request using the modified context
        $this->response = json_decode(file_get_contents(self::$API_BASE, false, $context));
    }

    public function getCurrentSong()
    {
        return $this->response->onNow->song->trackName;
    }

    public function getCurrentArtist()
    {
        return $this->response->onNow->song->artistName;
    }
    
    public function getStationName()
    {
        return "KUTX 98.9";
    }
    
    public function getStationDescription()
    {
        return "Austin's Music Discovery - The University of Texas at Austin Radio";
    }
    
    public function getStationWebsite()
    {
        return "https://kutx.org";
    }
    
    public function getStationLogo()
    {
        return "/logos/kutx.png";
    }
    
    public function getStationGenre()
    {
        return "Eclectic, Indie, Local Music";
    }
    
    public function getStationLocation()
    {
        return "Austin, TX";
    }

}
