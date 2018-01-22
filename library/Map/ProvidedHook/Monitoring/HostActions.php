<?php

namespace Icinga\Module\Map\ProvidedHook\Monitoring;

use Icinga\Module\Monitoring\DataView\Customvar;
use Icinga\Web\Controller\ModuleActionController;
use Icinga\Module\Monitoring\Hook\HostActionsHook;
use Icinga\Module\Monitoring\Object\Host;
use Icinga\Web\Url;

class HostActions extends HostActionsHook
{
    public function getActionsForHost(Host $host)
    {
        $actions = [];

        $host->fetchCustomvars();
        if (array_key_exists("geolocation", $host->customvars)) {
            $actions[t("Show on map")] = Url::fromPath('map/', array('showHost' => $host->host_name));
        }

        return $actions;
    }
}
