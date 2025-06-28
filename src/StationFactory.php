<?php

namespace LastPlayed;

class StationFactory
{
    private static array $stations = [
        'kexp' => callSigns\KEXP::class,
        'kndd' => callSigns\KNDD::class,
        'kzok' => callSigns\KZOK::class,
        'kpnw' => callSigns\KPNW::class,
        'kutx' => callSigns\KUTX::class,
        'ckkq' => callSigns\CKKQ::class,
        'kbcs' => callSigns\KBCS::class,
        'kbfg' => callSigns\KBFG::class,
        'kmgp' => callSigns\KMGP::class,
        'kxsu' => callSigns\KXSU::class,
    ];

    public static function create(string $callSign): ?model\RadioStation
    {
        $stationClass = self::$stations[strtolower($callSign)] ?? null;

        if (!$stationClass || !class_exists($stationClass)) {
            return null;
        }

        return new $stationClass();
    }
} 