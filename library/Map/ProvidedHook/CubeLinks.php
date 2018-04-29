<?php

namespace Icinga\Module\Map\ProvidedHook;

use Icinga\Data\Filter\Filter;
use Icinga\Module\Cube\Cube;
use Icinga\Module\Cube\Hook\ActionsHook;
use Icinga\Module\Cube\Ido\IdoHostStatusCube;
use Icinga\Web\View;

class CubeLinks extends ActionsHook
{
    /**
     * @inheritdoc
     */
    public function prepareActionLinks(Cube $cube, View $view)
    {
        if (! $cube instanceof IdoHostStatusCube) {
            return;
        }

        $vars = array("objectType"=>"host");
        foreach ($cube->getSlices() as $key => $val) {
            $vars['_host_' . $key] = $val;
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
