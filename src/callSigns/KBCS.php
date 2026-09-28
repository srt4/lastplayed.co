<?php

namespace LastPlayed\callSigns;

use LastPlayed\model;

class KBCS extends base\SpinitronStation {

    protected function getCallSign() 
    {
        return "KBCS";
    }

    public function getStationName() { return "91.3 KBCS"; }
    public function getStationDescription() { return "Bellevue College's listener-supported public radio station"; }
    public function getStationWebsite() { return "https://www.kbcs.fm"; }
    public function getStationListenUrl() { return "https://www.kbcs.fm/listen/live/"; }
    public function getStationLogo() { return "/logos/kbcs.png"; }
    public function getStationGenre() { return "Eclectic music, global beats, and acoustic traditions"; }
    public function getStationLocation() { return "Bellevue, WA"; }

}
