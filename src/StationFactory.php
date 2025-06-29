<?php

namespace LastPlayed;

class StationFactory
{
    private static array $stations = [
        'kexp' => callSigns\KEXP::class,
        'kndd' => callSigns\KNDD::class,
        'kzok' => callSigns\KZOK::class,
        'kutx' => callSigns\KUTX::class,
        'ckkq' => callSigns\CKKQ::class,
        'kbcs' => callSigns\KBCS::class,
        'kbfg' => callSigns\KBFG::class,
        'kmgp' => callSigns\KMGP::class,
        'kxsu' => callSigns\KXSU::class,
    ];

    public static function create(string $callSign): ?model\RadioStation
    {
        $callSign = strtolower(trim($callSign));
        
        if (!isset(self::$stations[$callSign])) {
            error_log("Station not found: $callSign");
            return null;
        }

        $stationClass = self::$stations[$callSign];
        
        if (!class_exists($stationClass)) {
            error_log("Station class not found: $stationClass");
            return null;
        }

        try {
            return new $stationClass();
        } catch (\Exception $e) {
            error_log("Error creating station $callSign: " . $e->getMessage());
            return null;
        }
    }
} 