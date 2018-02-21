<?php

namespace Icinga\Module\Map\ProvidedHook\Monitoring;

use Icinga\Module\Monitoring\Hook\ServiceActionsHook;
use Icinga\Module\Monitoring\Object\Service;
use Icinga\Web\Url;

class ServiceActions extends ServiceActionsHook
{
    public function getActionsForService(Service $service)
    {
        $actions = [];

        $service->fetchCustomvars();
        if (array_key_exists("geolocation", $service->customvars)) {
            $actions[t("Show on map")] = Url::fromPath('map/',
                array('showHost' => $service->host_name . "!" . $service->getName()));
        }

        return $actions;
    }
}
