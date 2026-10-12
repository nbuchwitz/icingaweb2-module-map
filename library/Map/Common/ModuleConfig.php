<?php

namespace Icinga\Module\Map\Common;

use Icinga\Application\Config;

/**
 * ModuleConfig is a helper class to access this module's configuration.
 */
class ModuleConfig
{
    public const DEFAULTS = [
        'cluster_problem_count' => 0,
        'default_lat' => '52.515855',
        'default_long' => '13.377485',
        'default_zoom' => 4,
        'max_native_zoom' => 19,
        'max_zoom' => 19,
        'min_zoom' => 2,
        'popup_mouseover' => 0,
        'stateType' => 'soft',
        'tile_url' => '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    ];

    /**
     * getMapsConfig returns the module's maps.ini configuration
     *
     * @return Config
     */
    public static function getMapsConfig(): Config
    {
        return Config::module('map', 'maps');
    }

    /**
     * getConfig returns the module's configuration
     *
     * @return Config
     */
    public static function getConfig(): Config
    {
        return Config::module('map');
    }
}
