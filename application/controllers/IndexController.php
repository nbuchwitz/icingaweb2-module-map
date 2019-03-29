<?php

namespace Icinga\Module\Map\Controllers;

use Icinga\Data\Filter\FilterException;
use Icinga\Web\Controller\ModuleActionController;

class IndexController extends ModuleActionController
{
    public function indexAction()
    {
        $config = $this->Config();
        $map = null;
        $mapConfig = null;

        // try to load stored map
        if ($this->params->has("load")) {
            $map = $this->params->get("load");

            if (!preg_match("/^[\w]+$/", $map)) {
                throw new FilterException("Invalid character in map name. Allow characters: a-zA-Z0-9_");
            }

            $mapConfig = $this->Config("maps");
            if (!$mapConfig->hasSection($map)) {
                throw new FilterException("Could not find stored map with name = " . $map);
            }
        }

        $this->view->id = uniqid();
        $this->view->host = $this->params->get("showHost");
        $this->view->expand = $this->params->get("expand");
        $this->view->fullscreen = ($this->params->get("showFullscreen") == 1);

        $parameterDefaults = array(
            "default_zoom" => "4",
            "default_long" => '13.377485',
            "default_lat" => '52.515855',
            "min_zoom" => "2",
            "max_zoom" => "19",
            "max_native_zoom" => "19",
            "disable_cluster_at_zoom" => null, // should be by default: max_zoom - 1
            "cluster_problem_count" => 0,
            "tile_url" => "//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            "opencage_apikey" => "",
        );

        /*
         * 1. url
         * 2. stored map
         * 3. user config
         * 4. config
         */
        $userPreferences = $this->Auth()->getUser()->getPreferences();
        if ($userPreferences->has("map")) {
            $config->getSection("map")->merge($userPreferences->get("map"));
        }

        foreach ($parameterDefaults as $parameter => $default) {
            if ($this->params->has($parameter)) {
                $this->view->$parameter = $this->params->get($parameter);
            } elseif (isset($map) && $mapConfig->getSection($map)->offsetExists($parameter)) {
                $this->view->$parameter = $mapConfig->get($map, $parameter);
            } else {
                $this->view->$parameter = $config->get("map", $parameter, $default);
            }
        }

        if (!$this->view->disable_cluster_at_zoom) {
            $this->view->disable_cluster_at_zoom = $this->view->max_zoom - 1;
        }

        $pattern = "/^([_]{0,1}(host|service))|\(|(object|state)Type/";
        $urlParameters = $this->filterArray($this->getAllParams(), $pattern);

        if (isset($map)) {
            $mapParameters = $this->filterArray($mapConfig->getSection($map)->toArray(), $pattern);
            $urlParameters = array_merge($mapParameters, $urlParameters);
        }

        array_walk($urlParameters, function (&$a, $b) {
            $a = $b . "=" . $a;
        });
        $this->view->url_parameters = join('&', $urlParameters);

        $this->view->dashletHeight = $config->get('map', 'dashlet_height', '300');
    }

    function filterArray($array, $pattern)
    {
        $matches = preg_grep($pattern, array_keys($array));
        return array_intersect_key($array, array_flip($matches));
    }
}
