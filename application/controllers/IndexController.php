<?php
namespace Icinga\Module\Map\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Web\Controller\ModuleActionController;
 
class IndexController extends ModuleActionController
{
    public function indexAction()
    {
        $config = $this->Config();
        $this->view->default_zoom = $config->get('map', 'default_zoom', '6');
        $this->view->default_long = $config->get('map', 'default_long', '13.409779');
        $this->view->default_lat = $config->get('map', 'default_lat', '52.520645');
        $this->view->min_zoom = $config->get('map', 'min_zoom', '5');
        $this->view->max_zoom = $config->get('map', 'max_zoom', '19');
    }
}
