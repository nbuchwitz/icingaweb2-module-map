<?php

namespace Icinga\Module\Map\Web\Controller;

use Icinga\Module\Map\ProvidedHook\Icingadb\IcingadbSupport;
use Icinga\Module\Map\Backend\Icingadb;
use Icinga\Module\Map\Backend\Monitoring;

use Icinga\Application\Modules\Module;

use ipl\Web\Compat\CompatController;

/**
 * MapController is used to support for the IcingaDB and Monitoring module.
 **/
abstract class MapController extends CompatController
{
    /** @var bool whether or not IcingaDB is set as backend */
    protected $isUsingIcingadb;

    /** @var Icingadb backend when IcingaDB is used */
    protected $icingadbBackend;

    /** @var Monitoring backend when Monitoring module is used */
    protected $monitoringBackend;

    /** @var string Pattern to check for broken coordinates */
    protected $coordinatePattern = '/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/';

    protected function moduleInit()
    {
        if (Module::exists('icingadb') && IcingadbSupport::useIcingaDbAsBackend()) {
            $this->isUsingIcingadb = true;
            $this->icingadbBackend = Icingadb::getInstance();

            return;
        }

        // If we didn't return before we assume the Monitoring module is used
        $this->monitoringBackend = new Monitoring();
    }
}
