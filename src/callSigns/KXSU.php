<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KXSU extends base\SpinitronStation {

    protected function getCallSign() 
    {
        return "KXSU";
    }

    public function getStationName() { return "KXSU 102.1 FM"; }
    public function getStationDescription() { return "Seattle University's student-run radio station"; }
    public function getStationWebsite() { return "https://www.kxsu.org"; }
    public function getStationListenUrl() { return "https://www.kxsu.org/#head-column-2"; }
    public function getStationLogo() { return "/logos/kxsu.png"; }
    public function getStationGenre() { return "Eclectic and local music"; }
    public function getStationLocation() { return "Seattle, WA"; }

}
