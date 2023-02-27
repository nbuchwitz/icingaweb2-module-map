<?php

namespace Icinga\Module\Map\Web\Controller;

use Icinga\Application\Modules\Module;
use Icinga\Module\Map\ProvidedHook\Icingadb\IcingadbSupport;
use Icinga\Module\Map\Util\IcingadbUtils;
use Icinga\Module\Monitoring\Controller;

abstract class MapController extends Controller
{
    /** @var bool whether icingadb is set as backend */
    protected $isUsingIcingadb;

    /** @var IcingadbUtils provide required icingadb utils */
    protected $icingadbUtils;

    /** @var string Pattern to check for broken coordinates */
    protected $coordinatePattern = '/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/';

    protected function moduleInit()
    {
        if (Module::exists('icingadb') && IcingadbSupport::useIcingaDbAsBackend()) {
            $this->isUsingIcingadb = true;
            $this->icingadbUtils = IcingadbUtils::getInstance();

            return;
        }

        parent::moduleInit();
    }
}