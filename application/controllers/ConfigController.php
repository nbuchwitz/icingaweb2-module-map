<?php

namespace Icinga\Module\Map\Controllers;

use Icinga\Module\Map\Forms\Config\GeneralConfigForm;
use Icinga\Module\Map\Forms\Config\DirectorConfigForm;
use Icinga\Web\Controller;

class ConfigController extends Controller
{
    public function init()
    {

    }

    public function indexAction()
    {
        $this->assertPermission('config/modules');
        $form = new GeneralConfigForm();
        $form->setIniConfig($this->Config());
        $form->handleRequest();

        $this->view->form = $form;
        $this->view->tabs = $this->Module()->getConfigTabs()->activate('config');
    }

    public function directorAction()
    {
        $this->assertPermission('map/director/configuration');
        $form = new DirectorConfigForm();
        $form->setIniConfig($this->Config());
        $form->handleRequest();

        $this->view->form = $form;
        $this->view->tabs = $this->Module()->getConfigTabs()->activate('director');
    }


    public function fetchAction()
    {
        $type = strtolower($this->params->shift('type', ""));
        $moduleConfig = $this->Config();
        $config = [];

        // TODO: Reuse the default values in config form?
        $defaults = array(
            "default_zoom" => "4",
            "default_long" => '13.377485',
            "default_lat" => '52.515855',
            "min_zoom" => "2",
            "max_zoom" => "19",
            "max_native_zoom" => "19",
            "cluster_problem_count" => 0,
            "tile_url" => "//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        );

        $defaults['disable_cluster_at_zoom'] = $defaults['max_zoom'] - 1; // should be by default: max_zoom - 1

        /*
         * Override module config with user's config
         */
        $userPreferences = $this->Auth()->getUser()->getPreferences();
        if ($userPreferences->has("map")) {
            $moduleConfig->getSection("map")->merge($userPreferences->get("map"));
        }

        if ($type === "director") {
            $moduleConfig->getSection("map")->merge($moduleConfig->getSection("director"));

            if($userPreferences->has("map-director")) {
                $moduleConfig->getSection("map")->merge($userPreferences->get("map-director"));
            }
        }


        foreach ($defaults as $parameter => $default) {
            $config[$parameter] = $moduleConfig->get("map", $parameter, $default);
        }

        print json_encode($config);
        exit();

    }
}

