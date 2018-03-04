<?php

namespace Icinga\Module\Map\ProvidedHook\Monitoring;

use Icinga\Module\Monitoring\Hook\ServiceActionsHook;
use Icinga\Module\Monitoring\Object\Service;
use Icinga\Web\Navigation\Navigation;
use Icinga\Web\Navigation\NavigationItem;
use Icinga\Web\Url;

class ServiceActions extends ServiceActionsHook
{
    public function getActionsForService(Service $service)
    {
        $nav = new Navigation();

        $service->fetchCustomvars();
        if (array_key_exists("geolocation", $service->customvars)) {
            $nav->addItem(new NavigationItem(t('Show on map'), array(
                'url' => Url::fromPath('map/', array('showHost' => $service->host_name . "!" . $service->getName())),
                'target' => '_next',
                'icon' => 'globe',
            )));
        }

        return $nav;
    }
}
