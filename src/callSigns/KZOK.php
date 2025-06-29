<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KZOK implements model\RadioStation {

    private static $API_BASE = "https://kzok.iheart.com/api/v4/player/live/7787/?sc=inferno&pname=KZOK-FM&theme=light&ihrnetwork=true&embed=true";

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
        // The iHeart API returns the song title in the response
        return $this->response->title;
    }

    public function getCurrentArtist()
    {
        // The iHeart API returns the artist in the response
        return $this->response->artist;
    }

}