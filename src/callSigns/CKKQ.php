<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class CKKQ implements model\RadioStation {

    // The Q's own now-playing widget reads this public JSONP feed.
    private static $API_BASE = "https://socast-public.s3.amazonaws.com/player/np_400_862.js";

    private $response; 

    public function __construct() 
    {
        $context = stream_context_create(['http' => ['timeout' => 6]]);
        $body = @file_get_contents(self::$API_BASE, false, $context);
        if ($body !== false && preg_match('/^jsonpcallback\((.*)\);?\s*$/s', trim($body), $matches)) {
            $this->response = json_decode($matches[1]);
        }
    }

    public function getCurrentSong()
    {
        return $this->response->song_name ?? null;
    }

    public function getCurrentArtist()
    {
        return $this->response->artist_name ?? null;
    }

    public function getRecentSongs()
    {
        // The streamon metadata feed only exposes the currently playing song
        return [];
    }

    public function getStationName()
    {
        return "100.3 The Q!";
    }

    public function getStationDescription()
    {
        return "The Island's Rock, broadcasting from Victoria";
    }

    public function getStationWebsite()
    {
        return "https://www.theq.fm";
    }

    public function getStationListenUrl()
    {
        return "https://www.theq.fm/listen/";
    }

    public function getStationLogo()
    {
        return "/logos/ckkq.png";
    }

    public function getStationGenre()
    {
        return "Rock";
    }

    public function getStationLocation()
    {
        return "Victoria, BC";
    }

    protected function getCallSign()
    {
        return "CKKQ";
    }

}
