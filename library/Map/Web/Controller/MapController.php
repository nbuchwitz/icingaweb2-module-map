<?php

namespace Icinga\Module\Map\Web\Controller;

use Icinga\Application\Modules\Module;
use Icinga\Module\Icingadb\Common\Auth;
use Icinga\Module\Icingadb\Common\Database;
use Icinga\Module\Map\ProvidedHook\Icingadb\IcingadbSupport;
use Icinga\Module\Monitoring\Controller;
use ipl\Orm\Query;
use ipl\Orm\UnionQuery;
use ipl\Stdlib\Filter;
use ipl\Web\Filter\QueryString;

abstract class MapController extends Controller
{
    use Database;
    use Auth;

    /** @var bool whether icingadb is set as backend */
    protected $isUsingIcingadb;

    /** @var string Pattern to check for broken coordinates */
    protected $coordinatePattern = '/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/';

    /** @var Filter\Rule Filter from query string parameters */
    private $filter;

    protected function moduleInit()
    {
        if (Module::exists('icingadb') && IcingadbSupport::useIcingaDbAsBackend()) {
            $this->isUsingIcingadb = true;

            return;
        }

        parent::moduleInit();
    }

    /**
     * Get the filter created from query string parameters
     *
     * @return Filter\Rule
     */
    public function getFilter(): Filter\Rule
    {
        if ($this->filter === null) {
            $this->filter = QueryString::parse((string) $this->params);
        }

        return $this->filter;
    }

    public function filter(Query $query, Filter\Rule $filter = null): self
    {
        if ($this->hasPermission('config/authentication/roles/show')) {
            $this->applyRestrictions($query);
        }

        if ($query instanceof UnionQuery) {
            foreach ($query->getUnions() as $query) {
                $query->filter($filter ?: $this->getFilter());
            }
        } else {
            $query->filter($filter ?: $this->getFilter());
        }

        return $this;
    }
}