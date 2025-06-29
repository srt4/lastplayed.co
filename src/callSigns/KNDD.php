<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KNDD implements model\RadioStation {

    private static $API_BASE = "https://api-nowplaying.amperwave.net/prt/nowplaying/2/5/7028/nowplaying.json";

    private $response; 

    public function __construct() 
    {
        // Set Chrome user agent header
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
        // Get the first (most recent) performance from the list
        return $this->response->performances[0]->title;
    }

    public function getCurrentArtist()
    {
        // Get the first (most recent) performance from the list
        return $this->response->performances[0]->artist;
    }

}