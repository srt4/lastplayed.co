<?php

namespace callSigns;

use model; 

class KPNW implements model\RadioStation { 
 
    private static $API_BASE = "https://live.989kpnw.com/wp-content/plugins/hubbard-listen-live/api/hll_cues_get.php"; 
    
    private $response; 

    public function __construct() 
    {
        $this->response = json_decode(file_get_contents(self::$API_BASE));
    }
    
    public function getCurrentSong()
    {
        return $this->response->data->response[0]->data->description;
    }

    public function getCurrentArtist()
    {
        return $this->response->data->response[0]->data->artist;
    }
  
}
