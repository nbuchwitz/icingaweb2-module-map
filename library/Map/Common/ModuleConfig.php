<?php

namespace Icinga\Module\Map\Common;

use Icinga\Application\Config;
use Icinga\Application\Logger;
use Icinga\Authentication\Auth;

use Exception;

/**
 * ModuleConfig is a helper class to safely access this module's configuration.
 */
class ModuleConfig
{
    // These options can be overwritten via URL parameters
    public const URL_OVERRIDABLE_PARAMS = [
        'cluster_problem_count',
        'default_lat',
        'default_long',
        'default_zoom',
        'disable_cluster_at_zoom',
        'max_native_zoom',
        'max_zoom',
        'min_zoom',
        'popup_mouseover',
        'stateType',
        'tile_url',
    ];

    /**
     * getMapsConfig returns the module's maps configuration
     *
     * @return Config
     */
    public static function getMapsConfig(): Config
    {
        return Config::module('maps');
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

    /**
     * getConfigWithDefaults loads all configuration options with their defaults.
     *
     * @return array
     */
    public static function getConfigWithDefaults(?Config $moduleConfig = null): Config
    {
        $default = [
            'cluster_problem_count' => false,
            'default_lat' => '52.515855',
            'default_long' => '13.377485',
            'default_zoom' => 4,
            'max_native_zoom' => 19,
            'max_zoom' => 19,
            'min_zoom' => 2,
            'opencage_apikey' => '',
            'popup_mouseover' => false,
            'tile_url' => '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        ];

        $default['disable_cluster_at_zoom'] = $default['max_zoom'] - 1;

        // Try to load the configuration
        if ($moduleConfig === null) {
            try {
                $moduleConfig = self::getConfig();
                Logger::debug('Loaded Map module configuration');
            } catch (Exception $e) {
                Logger::error('Failed to load Map module configuration: %s', $e);
                return $default;
            }
        }

        $config = [];

        $config['cluster_problem_count'] = (bool) $moduleConfig->get('map', 'cluster_problem_count', $default['cluster_problem_count']);
        $config['default_lat'] = $moduleConfig->get('map', 'default_lat', $default['default_lat']);
        $config['default_long'] = $moduleConfig->get('map', 'default_long', $default['default_long']);
        $config['default_zoom'] = (int) $moduleConfig->get('map', 'default_zoom', $default['default_zoom']);
        $config['max_native_zoom'] = (int) $moduleConfig->get('map', 'max_native_zoom', $default['max_native_zoom']);
        $config['max_zoom'] = (int) $moduleConfig->get('map', 'max_zoom', $default['max_zoom']);
        $config['min_zoom'] = (int) $moduleConfig->get('map', 'min_zoom', $default['min_zoom']);
        $config['disable_cluster_at_zoom'] = (int) $moduleConfig->get('map', 'disable_cluster_at_zoom', $default['disable_cluster_at_zoom']);
        $config['opencage_apikey'] = $moduleConfig->get('map', 'opencage_apikey', $default['opencage_apikey']);
        $config['popup_mouseover'] = (bool) $moduleConfig->get('map', 'popup_mouseover', $default['popup_mouseover']);
        $config['tile_url'] = $moduleConfig->get('map', 'tile_url', $default['tile_url']);

        return Config::fromArray($config);
    }
}
