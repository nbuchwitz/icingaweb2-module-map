<?php

namespace Icinga\Module\Map\ProvidedHook\Icingadb;

use Icinga\Module\Icingadb\Hook\HostActionsHook;
use Icinga\Module\Icingadb\Model\Host;
use ipl\Web\Widget\Icon;
use ipl\Web\Widget\Link;

class HostActions extends HostActionsHook
{
    public function getActionsForObject(Host $host): array
    {
        if (! isset($host->vars['geolocation'])) {
            return [];
        }

        $label = mt('map', 'Show on map');
        return [
            new Link(
                [new Icon('globe'), $label],
                'map?showHost=' . rawurlencode($host->name)
            )
        ];
    }
}
