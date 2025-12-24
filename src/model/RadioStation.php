<?php

namespace LastPlayed\model;

interface RadioStation {

    public function getCurrentSong();
    public function getCurrentArtist();
    public function getStationName();
    public function getStationDescription();
    public function getStationWebsite();
    public function getStationLogo();
    public function getStationGenre();
    public function getStationLocation();

}