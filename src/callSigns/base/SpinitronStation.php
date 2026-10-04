<?php

namespace LastPlayed\callSigns\base;

use LastPlayed\model;

abstract class SpinitronStation implements model\RadioStation {

    private static $BASE_URL = "https://widgets.spinitron.com/";

    private $song;
    private $artist;
    private $recentSongs = [];

    public function __construct()
    {
        $responseBody = file_get_contents("https://widgets.spinitron.com/"
            . strtoupper($this->getCallSign()) . "/");
        $songArtist = array(); // artist is index 1, song is index 2
        preg_match("/class=\"artist\">(.+?)<.+?class=\"song\">(.+?)</", $responseBody, $songArtist);
        $this->artist = $songArtist[1] ?? null;
        $this->song = $songArtist[2] ?? null;

        // Each spin row carries a data-spin='{"i":...,"a":artist,"s":song,"r":...}'
        // attribute plus a spin-time cell like "9:39 AM", ordered most recent
        // first; the first row is the current song. Times are rendered in the
        // station's local timezone with no date, so dates are reconstructed by
        // walking backwards from now and rolling back a day whenever a time
        // would otherwise be later than the spin that followed it.
        $rows = array();
        preg_match_all(
            '/class="spin-item"[^>]*data-spin="([^"]+)"[^>]*>\s*<td class="spin-time">\s*<a[^>]*>([^<]+)<\/a>/s',
            $responseBody,
            $rows,
            PREG_SET_ORDER
        );

        $timezone = new \DateTimeZone($this->getStationTimezone());
        $reference = new \DateTime('now', $timezone);

        foreach (array_slice($rows, 1) as $row) {
            $spin = json_decode(html_entity_decode($row[1]));
            if (!$spin || empty($spin->a) || empty($spin->s)) {
                continue;
            }

            $timestamp = null;
            $airdate = \DateTime::createFromFormat('g:i A', trim($row[2]), $timezone);
            if ($airdate) {
                while ($airdate > $reference) {
                    $airdate->modify('-1 day');
                }
                $reference = clone $airdate;
                $timestamp = $airdate->format(\DateTime::ATOM);
            }

            $this->recentSongs[] = [
                'song' => $spin->s,
                'artist' => $spin->a,
                'timestamp' => $timestamp,
                'timestampSource' => 'estimated',
            ];
        }
    }

    public function getCurrentSong()
    {
        return $this->song;
    }

    public function getCurrentArtist()
    {
        return $this->artist;
    }

    public function getRecentSongs()
    {
        return $this->recentSongs;
    }

    protected abstract function getCallSign();

    // The Spinitron widget renders spin times in the station's local timezone
    protected function getStationTimezone()
    {
        return 'America/Los_Angeles';
    }

    // Each station supplies its own verified metadata.
    abstract public function getStationName();
    abstract public function getStationDescription();
    abstract public function getStationWebsite();
    abstract public function getStationListenUrl();
    abstract public function getStationLogo();
    abstract public function getStationGenre();
    abstract public function getStationLocation();

}
