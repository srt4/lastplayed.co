<?php

namespace callSigns;

use model;

class KEXP implements model\RadioStation {

    private static $API_BASE = "https://legacy-api.kexp.org/play/?limit=1&ordering=-airdate&offset=0";

    public function getCurrentSong()
    {
        $response = file_get_contents(self::$API_BASE);
        $response = json_decode($response);

        $song = $response->results[0]->track->name;
        return $song;
    }

    public function getCurrentArtist()
    {
        $response = file_get_contents(self::$API_BASE);
        $response = json_decode($response);

        $artist = $response->results[0]->artist->name;
        return $artist;
    }

}
