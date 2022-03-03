<?php
namespace Icinga\Module\Map\ProvidedHook;

use Icinga\Module\Cube\Cube;
use Icinga\Module\Cube\Hook\ActionsHook;
use Icinga\Module\Cube\IcingaDb\IcingaDbHostStatusCube;
use Icinga\Web\View;

class IcingaDbCubeLinks extends ActionsHook
{
    /**
     * @inheritdoc
     */
    public function prepareActionLinks(Cube $cube, View $view)
    {
        if (! $cube instanceof IcingaDbHostStatusCube) {
            return;
        }

        $vars = ["objectType"=>"host"];
        foreach ($cube->getSlices() as $dimension => $slice) {
            $vars['host.vars.' . $dimension] = trim($slice, '"');
        }

        $url = 'map';

        $this->addActionLink(
            $this->makeUrl($url, $vars),
            $view->translate('Show on map'),
            $view->translate('This shows all matching hosts and their current state on the map module'),
            'globe'
        );
    }
}