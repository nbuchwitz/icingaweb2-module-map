<?php

namespace Icinga\Module\Map\Backend;

use Icinga\Module\Monitoring\Backend\MonitoringBackend;

use Icinga\Authentication\Auth;
use Icinga\Exception\ConfigurationError;
use Icinga\Exception\QueryException;
use Icinga\Data\Filter\Filter;
use Icinga\Data\Filterable;

use ipl\I18n\Translation;

/**
 * Monitoring is used to initialize the Monitoring module backend
 */
class Monitoring
{
    use Translation;

    protected ?Auth $auth = null;
    protected ?MonitoringBackend $backend = null;

    public function __construct()
    {
        $this->backend = MonitoringBackend::instance();
    }

    public function getAuth()
    {
        if ($this->auth === null) {
            $this->auth = Auth::getInstance();
        }
        return $this->auth;
    }

    public function getRestrictions(string $name)
    {
        return $this->getAuth()->getRestrictions($name);
    }

    /**
     * getDb returns the MonitoringBackend. We use getDb as a name
     * to have the same name as the IcingaDB backend
     */
    public function getDb()
    {
        return $this->backend;
    }

    /**
     * Apply a restriction of the authenticated on the given filterable
     *
     * @param string $name Name of the restriction
     * @param Filterable $filterable Filterable to restrict
     *
     * @return Filterable The filterable having the restriction applied
     */
    public function applyRestriction(string $name, Filterable $filterable)
    {
        $filterable->applyFilter($this->getRestriction($name));
        return $filterable;
    }

    /**
     * Get a restriction of the authenticated
     *
     * @param string $name Name of the restriction
     *
     * @return Filter Filter object
     * @throws ConfigurationError If the restriction contains invalid filter columns
     */
    protected function getRestriction(string $name)
    {
        $restriction = Filter::matchAny();
        $restriction->setAllowedFilterColumns([
            'host_name',
            'hostgroup_name',
            'instance_name',
            'service_description',
            'servicegroup_name',
            function ($c) {
                return preg_match('/^_(?:host|service)_/i', $c);
            }
        ]);
        foreach ($this->getRestrictions($name) as $filter) {
            if ($filter === '*') {
                return Filter::matchAll();
            }
            try {
                $restriction->addFilter(Filter::fromQueryString($filter));
            } catch (QueryException $e) {
                throw new ConfigurationError(
                    $this->translate(
                        'Cannot apply restriction %s using the filter %s. You can only use the following columns: %s'
                    ),
                    $name,
                    $filter,
                    implode(', ', [
                        'instance_name',
                        'host_name',
                        'hostgroup_name',
                        'service_description',
                        'servicegroup_name',
                        '_(host|service)_<customvar-name>'
                    ]),
                    $e
                );
            }
        }

        if ($restriction->isEmpty()) {
            return Filter::matchAll();
        }

        return $restriction;
    }
}
