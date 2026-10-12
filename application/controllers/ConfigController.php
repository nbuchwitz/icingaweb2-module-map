<?php

namespace Icinga\Module\Map\Controllers;

use Icinga\Module\Map\Common\ModuleConfig;
use Icinga\Module\Map\Forms\Config\DirectorConfigForm;
use Icinga\Module\Map\Forms\Config\GeneralConfigForm;

use Icinga\Application\Logger;
use Icinga\Util\Json;
use Icinga\Exception\Json\JsonEncodeException;
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

        $config = ModuleConfig::getConfig();

        $form = new GeneralConfigForm();
        $form->setIniConfig($config);
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

        $config = ModuleConfig::getConfig();

        $form = new DirectorConfigForm();
        $form->setIniConfig($config);
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
        $moduleConfig = ModuleConfig::getConfig();
        $config = [];

        $defaults = ModuleConfig::DEFAULTS;
        $defaults['disable_cluster_at_zoom'] = $defaults['max_zoom'] - 1; // should be by default: max_zoom - 1

        // Override module config with user's preferences
        $userPreferences = $this->Auth()->getUser()->getPreferences();
        if ($userPreferences->has("map")) {
            $moduleConfig->getSection("map")->merge($userPreferences->get("map"));
        }

        // Merge director and user's preferences
        if ($type === "director") {
            $moduleConfig->getSection("map")->merge($moduleConfig->getSection("director"));
            if ($userPreferences->has("map-director")) {
                $moduleConfig->getSection("map")->merge($userPreferences->get("map-director"));
            }
        }

        foreach ($defaults as $parameter => $default) {
            $config[$parameter] = $moduleConfig->get("map", $parameter, $default);
        }

        try {
            print Json::encode($config);
        } catch (JsonEncodeException $e) {
            Logger::error('Failed to encode configuration: %s', $e);
        }

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
