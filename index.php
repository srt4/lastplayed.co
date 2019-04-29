<?php

$callSign = $_GET['call_sign'];

if ((@include "callSigns/" . strtolower($callSign) . ".php") === false) {
   exit ("Unknown station"); 
} 

?>

