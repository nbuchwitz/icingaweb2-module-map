<?php

namespace Icinga\Module\Map\ProvidedHook\Monitoring;

use Icinga\Module\Monitoring\DataView\Customvar;
use Icinga\Module\Monitoring\Hook\HostActionsHook;
use Icinga\Module\Monitoring\Object\Host;
use Icinga\Web\Controller\ModuleActionController;
use Icinga\Web\Navigation\Navigation;
use Icinga\Web\Navigation\NavigationItem;
use Icinga\Web\Url;

class HostActions extends HostActionsHook
{
    /**
     * getActionsForHost adds an action link for the given object if
     * it has a geolocation associated.
     **/
    public function getActionsForHost(Host $host)
    {
        $nav = new Navigation();

        $host->fetchCustomvars();
        if (array_key_exists("geolocation", $host->customvars)) {
            $nav->addItem(new NavigationItem(t('Show on map'), array(
                'url' => Url::fromPath('map/', array('showHost' => $host->getName())),
                'target' => '_next',
                'icon' => 'globe',
            )));
        }

        return $nav;
    }
}
