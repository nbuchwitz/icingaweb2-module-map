<?php

namespace Icinga\Module\Map\Controllers;

use Icinga\Module\Map\Forms\Config\DirectorConfigForm;
use Icinga\Module\Map\Forms\Config\GeneralConfigForm;
use Icinga\Web\Widget\Tabs;

use ipl\Html\HtmlString;
use ipl\Web\Compat\CompatController;

class ConfigController extends CompatController
{
    public function init()
    {
    }

    /**
     * indexAction is used on the Icinga Web configuration page
     * to display the Map module configuration form.
     **/
    public function indexAction(): void
    {
        $this->assertPermission('config/modules');
        $form = new GeneralConfigForm();
        $form->setIniConfig($this->Config());
        $form->handleRequest();

        $this->addContent(HtmlString::create($form));

        $this->mergeTabs($this->Module()->getConfigTabs());
        $this->getTabs()->activate('config');
    }

    /**
     * directorAction is used on the Icinga Web configuration page
     * to display the Director specific Map module configuration form.
     **/
    public function directorAction(): void
    {
        $this->assertPermission('map/director/configuration');
        $form = new DirectorConfigForm();
        $form->setIniConfig($this->Config());
        $form->handleRequest();

        $this->addContent(HtmlString::create($form));

        $this->mergeTabs($this->Module()->getConfigTabs());
        $this->getTabs()->activate('director');
    }

    /**
     * fetchAction is used in the mapDatatype module to fetch this module's
     * configuration in the mapDatatype's module.js
     **/
    public function fetchAction(): void
    {
        $type = strtolower($this->params->shift('type', ""));
        $moduleConfig = $this->Config();
        $config = [];

        // TODO: Reuse the default values in config form?
        $defaults = [
            "default_zoom" => "4",
            "default_long" => '13.377485',
            "default_lat" => '52.515855',
            "min_zoom" => "2",
            "max_zoom" => "19",
            "max_native_zoom" => "19",
            "cluster_problem_count" => 0,
            "popup_mouseover" => 0,
            "tile_url" => "//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        ];

        $defaults['disable_cluster_at_zoom'] = $defaults['max_zoom'] - 1; // should be by default: max_zoom - 1

        // Override module config with user's config
        $userPreferences = $this->Auth()->getUser()->getPreferences();
        if ($userPreferences->has("map")) {
            $moduleConfig->getSection("map")->merge($userPreferences->get("map"));
        }

        if ($type === "director") {
            $moduleConfig->getSection("map")->merge($moduleConfig->getSection("director"));

            if ($userPreferences->has("map-director")) {
                $moduleConfig->getSection("map")->merge($userPreferences->get("map-director"));
            }
        }

        foreach ($defaults as $parameter => $default) {
            $config[$parameter] = $moduleConfig->get("map", $parameter, $default);
        }

        print json_encode($config);
        exit();
    }

    /**
     * Merge given tabs with controller tabs
     *
     * @param Tabs $tabs
     *
     * @return void
     */
    protected function mergeTabs(Tabs $tabs): void
    {
        foreach ($tabs->getTabs() as $tab) {
            $this->getTabs()->add($tab->getName(), $tab);
        }
    }
}
