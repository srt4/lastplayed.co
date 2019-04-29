<?php

$response = file_get_contents("https://legacy-api.kexp.org/play/?limit=1&ordering=-airdate&offset=0"); 

$response = json_decode($response); 

$song = $response->results[0]->track->name;
$artist = $response->results[0]->artist->name; 

?> 

<h2 style="font-family: sans-serif">Song: <?=$song?></h2>
<h2 style="font-family: sans-serif">Artist: <?=$artist?></h2>
