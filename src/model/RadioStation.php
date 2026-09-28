<?php

namespace LastPlayed\model;

interface RadioStation {

    public function getCurrentSong();
    public function getCurrentArtist();

    /**
     * Returns an array of recently played songs, most recent first,
     * excluding the currently playing song. Each entry is:
     * ['song' => string, 'artist' => string, 'timestamp' => string|null]
     * where timestamp is ISO 8601, or null when the source has no times.
     */
    public function getRecentSongs();
    public function getStationName();
    public function getStationDescription();
    public function getStationWebsite();
    public function getStationListenUrl();
    public function getStationLogo();
    public function getStationGenre();
    public function getStationLocation();

}
