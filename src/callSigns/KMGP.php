<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KMGP extends base\SpinitronStation {

    protected function getCallSign() 
    {
        return "KMGP";
    }

    public function getStationName() { return "SPACE 101.1 FM"; }
    public function getStationDescription() { return "People-powered arts and music radio from Magnuson Park"; }
    public function getStationWebsite() { return "https://www.space101fm.org"; }
    public function getStationListenUrl() { return "https://www.space101fm.org/listen-live/"; }
    public function getStationLogo() { return "/logos/kmgp.png"; }
    public function getStationGenre() { return "Eclectic music and arts"; }
    public function getStationLocation() { return "Seattle, WA"; }

}
