<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KPNW implements model\RadioStation {

    private static $API_BASE = "https://live.amperwave.net/playlist/KPNW-AAC";
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
        return $this->response->data->response[0]->data->description;
    }

    public function getCurrentArtist()
    {
        return $this->response->data->response[0]->data->artist;
    }

}
