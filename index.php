<?php

require_once('model/RadioStation.php');
require_once('callSigns/base/SpinitronStation.php'); 

$callSign = $_GET['call_sign'];

// error validation - in case it's not implemented
if ((@include "callSigns/" . strtolower($callSign) . ".php") === false) {
   exit ("Unknown station");
}

$stationClass = "\\callSigns\\" . strtoupper($callSign);
$station = new $stationClass();
?>

<h2 style="font-family: sans-serif">Song: <?=$station->getCurrentSong()?></h2>
<h2 style="font-family: sans-serif">Artist: <?=$station->getCurrentArtist()?></h2>
