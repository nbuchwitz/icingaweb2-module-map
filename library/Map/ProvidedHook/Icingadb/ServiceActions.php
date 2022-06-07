<?php

namespace Icinga\Module\Map\ProvidedHook\Icingadb;

use Icinga\Module\Icingadb\Hook\ServiceActionsHook;
use Icinga\Module\Icingadb\Model\Service;
use ipl\Web\Widget\Icon;
use ipl\Web\Widget\Link;

class ServiceActions extends ServiceActionsHook
{
    public function getActionsForObject(Service $service): array
    {
        if (! isset($service->vars['geolocation'])) {
            return [];
        }

        $label = mt('map', 'Show on map');
        return [
            new Link(
                [new Icon('globe'), $label],
                'map?showHost=' . rawurlencode($service->host->name . '!' . $service->name)
            )
        ];
    }
}
