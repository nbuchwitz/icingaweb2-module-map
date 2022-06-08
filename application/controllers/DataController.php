<?php

namespace Icinga\Module\Map\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Icingadb\Model\Service;
use Icinga\Module\Icingadb\Redis\VolatileStateResults;
use Icinga\Module\Map\Web\Controller\MapController;
use Icinga\Module\Monitoring\DataView\DataView;
use ipl\Orm\Model;
use ipl\Stdlib\Filter as IplFilter;

class DataController extends MapController
{
    /**
     * Apply filters on a DataView
     *
     * @param DataView $dataView The DataView to apply filters on
     *
     * @return DataView $dataView
     */
    protected function filterQuery(DataView $dataView)
    {
        $this->setupFilterControl($dataView, null, null, ['stateType', 'objectType', 'problems']);
        return $dataView;
    }

    private $stateColumn;
    private $stateChangeColumn;
    private $filter;
    private $points = [];

    /**
     * Get JSON state objects
     */
    public function pointsAction()
    {
        try {
            // Borrowed from monitoring module
            // Handle soft and hard states
            $config = $this->config();
            $stateType = strtolower($this->params->shift('stateType',
                $config->get('map', 'stateType', 'soft')
            ));

            $userPreferences = $this->Auth()->getUser()->getPreferences();
            if ($userPreferences->has("map")) {
                $stateType = $userPreferences->getValue("map", "stateType", $stateType);
            }

            $this->filter = (bool)$this->params->shift('problems', false) ? Filter::where('service_problem', 1) : null;

            $objectType = strtolower($this->params->shift('objectType', 'all'));

            if ($stateType === 'hard') {
                $this->stateColumn = 'hard_state';
                $this->stateChangeColumn = 'last_hard_state_change';
                if ($this->isUsingIcingadb) {
                    $this->stateChangeColumn = 'last_state_change';
                }
            } else {
                $this->stateColumn = 'state';
                $this->stateChangeColumn = 'last_state_change';
                if ($this->isUsingIcingadb) {
                    $this->stateColumn = 'soft_state';
                }
            }

            if (in_array($objectType, ['all', 'host'])) {
                if ($this->isUsingIcingadb) {
                    $this->addIcingadbHostsToPoints();
                } else {
                    $this->addHostsToPoints();
                }
            }

            if (in_array($objectType, ['all', 'service'])) {
                if ($this->isUsingIcingadb) {
                   $this->addIcingadbServicesToPoints();
                } else {
                    $this->addServicesToPoints();
                }
            }
        } catch (\Exception $e) {
            $this->points['message'] = $e->getMessage();
            $this->points['trace'] = $e->getTraceAsString();
        }

        echo json_encode($this->points);
        exit();
    }

    private function addHostsToPoints()
    {
        // get host data
        $hostQuery = $this->backend
            ->select()
            ->from('hoststatus', array(
                'host_display_name',
                'host_name',
                'host_acknowledged',
                'host_state' => 'host_' . $this->stateColumn,
                'host_last_state_change' => 'host_' . $this->stateChangeColumn,
                'host_in_downtime',
                'host_problem',
                'coordinates' => '_host_geolocation',
                'icon' => '_host_map_icon',
            ))
            ->applyFilter(Filter::fromQueryString('_host_geolocation >'));

        $this->applyRestriction('monitoring/filter/objects', $hostQuery);
        $this->filterQuery($hostQuery);

        // get service data
        $serviceQuery = $this->backend
            ->select()
            ->from('servicestatus', array(
                'host_name',
                'service_display_name',
                'service_name' => 'service',
                'service_acknowledged',
                'service_state' => 'service_' . $this->stateColumn,
                'service_last_state_change' => 'service_' . $this->stateChangeColumn,
                'service_in_downtime'
            ))
            ->applyFilter(Filter::fromQueryString('_host_geolocation >'));

        if ($this->filter) {
            $serviceQuery->applyFilter($this->filter);
        }

        $this->applyRestriction('monitoring/filter/objects', $serviceQuery);
        $this->filterQuery($serviceQuery);

        if ($hostQuery->count() > 0) {
            foreach ($hostQuery as $row) {
                $hostname = $row->host_name;

                $host = (array)$row;
                $host['services'] = array();

                if (!preg_match($this->coordinatePattern, $host['coordinates'])) {
                    continue;
                }

                $host['coordinates'] = explode(",", $host['coordinates']);

                $this->points['hosts'][$hostname] = $host;
            }
        }

        // add services to host
        if ($serviceQuery->count() > 0) {
            foreach ($serviceQuery as $row) {
                $hostname = $row->host_name;

                $service = (array)$row;
                unset($service['host_name']);

                if (isset($this->points['hosts'][$hostname])) {
                    $this->points['hosts'][$hostname]['services'][$service['service_display_name']] = $service;
                }
            }
        }

        // remove hosts without problems and services
        if ($this->filter) {
            foreach ($this->points['hosts'] as $name => $host) {
                if (empty($host['services']) && $host['host_problem'] != '1') {
                    unset($this->points['hosts'][$name]);
                }
            }
        }
    }

    private function addServicesToPoints()
    {
        // get services with geolocation
        $geoServiceQuery = $this->backend
            ->select()
            ->from('servicestatus', array(
                'host_display_name',
                'host_name',
                'host_acknowledged',
                'host_state' => 'host_' . $this->stateColumn,
                'host_last_state_change' => 'host_' . $this->stateChangeColumn,
                'host_in_downtime',
                'service_display_name',
                'service_name' => 'service',
                'service_acknowledged',
                'service_state' => 'service_' . $this->stateColumn,
                'service_last_state_change' => 'service_' . $this->stateChangeColumn,
                'service_in_downtime',
                'coordinates' => '_service_geolocation',
                'icon' => '_service_map_icon',

            ))->applyFilter(Filter::fromQueryString('_service_geolocation >'));

        if ($this->filter) {
            $geoServiceQuery->applyFilter($this->filter);
        }


        $this->applyRestriction('monitoring/filter/objects', $geoServiceQuery);
        $this->filterQuery($geoServiceQuery);
        // ---

        if ($geoServiceQuery->count() > 0) {
            foreach ($geoServiceQuery as $row) {
                $identifier = $row->host_name . "!" . $row->service_name;

                $ar = (array)$row;

                $host = array_filter($ar, function ($k) {
                    return (preg_match("/^host_|^coordinates/", $k));
                }, ARRAY_FILTER_USE_KEY);

                $service = array_filter($ar, function ($k) {
                    return (preg_match("/^service_/", $k));
                }, ARRAY_FILTER_USE_KEY);

                $host['services'][$service['service_display_name']] = $service;

                if (!preg_match($this->coordinatePattern, $host['coordinates'])) {
                    continue;
                }

                $host['coordinates'] = explode(",", $host['coordinates']);
                $host['icon'] = $ar['icon'];
                $this->points['services'][$identifier] = $host;
            }
        }
    }

    private function addIcingadbHostsToPoints()
    {
        $hostQuery = Host::on($this->icingadbUtils->getDb())->with(['state']);
        $hostQuery->setResultSetClass(VolatileStateResults::class);
        $hostQuery->filter(IplFilter::equal('host.vars.geolocation', '*'));
        //TODO not working properly
        $this->Filter($hostQuery, $this->getFilter());
        $hostQuery = $hostQuery->execute();
        if ($hostQuery->hasResult()) {
            foreach ($hostQuery as $row) {
                $hostname = $row->name;
                $host = $this->populateObjectColumnsToArray($row);
                $host['host_problem']               = $row->state->is_problem ? 1 : 0;
                $host['coordinates']                = $row->vars['geolocation'];
                $host['icon']                       = $row->vars['map_icon'] ?? null;

                $host['services'] = [];
                if (! preg_match($this->coordinatePattern, $host['coordinates'])) {
                    continue;
                }

                $host['coordinates'] = explode(",", $host['coordinates']);

                $this->points['hosts'][$hostname] = $host;
            }
        }

        // add services to host
        $serviceQuery = Service::on($this->icingadbUtils->getDb())->with(['state', 'host', 'host.state']);
        $serviceQuery->setResultSetClass(VolatileStateResults::class);
        $serviceQuery->filter(IplFilter::equal('host.vars.geolocation', '*'));

        if ($this->filter) {
            $serviceQuery->Filter(IplFilter::equal('state.is_problem', 'y'));
        }

        $this->Filter($serviceQuery, $this->getFilter());
        $serviceQuery = $serviceQuery->execute();
        if ($serviceQuery->hasResult()) {
            foreach ($serviceQuery as $row) {
                $hostname = $row->host->name;
                $service = $this->populateObjectColumnsToArray($row);
                if (isset($this->points['hosts'][$hostname])) {
                    $this->points['hosts'][$hostname]['services'][$service['service_display_name']] = $service;
                }
            }
        }

        // remove hosts without problems and services
        if ($this->filter) {
            foreach ($this->points['hosts'] as $name => $host) {
                if (empty($host['services']) && $host['host_problem'] !== 1) {
                    unset($this->points['hosts'][$name]);
                }
            }
        }
    }

    private function addIcingadbServicesToPoints()
    {
        $serviceQuery = Service::on($this->icingadbUtils->getDb())->with(['state', 'host']);
        $serviceQuery->setResultSetClass(VolatileStateResults::class);
        $serviceQuery->filter(IplFilter::equal('service.vars.geolocation', '*'));

        if ($this->filter) {
            $serviceQuery->Filter(IplFilter::equal('service.state.is_problem', 'y'));
        }

        $this->Filter($serviceQuery, $this->getFilter());
        $serviceQuery = $serviceQuery->execute();
        if ($serviceQuery->hasResult()) {
            foreach ($serviceQuery as $row) {
                $identifier = $row->host->name . "!" . $row->name;
                $host = $this->populateObjectColumnsToArray($row->host);
                $host['coordinates'] = $row->vars['geolocation'];
                $host['icon'] = $row->vars['map_icon'] ?? null;

                $service = $this->populateObjectColumnsToArray($row);

                $host['services'][$service['service_display_name']] = $service;

                if (! preg_match($this->coordinatePattern, $host['coordinates'])) {
                    continue;
                }

                $host['coordinates'] = explode(",", $host['coordinates']);

                $this->points['services'][$identifier] = $host;
            }
        }
    }

    /**
     * @param Model $object
     *
     * @return array
     */
    private function populateObjectColumnsToArray(Model $object)
    {
        $objectType = $object instanceof Service ? 'service' : 'host';

        $stateColumn = $this->stateColumn;
        $lastStateChangeColumn = $this->stateChangeColumn;

        $obj = [];

        $obj["{$objectType}_display_name"]        = $object->display_name;
        $obj["{$objectType}_name"]                = $object->name;
        $obj["{$objectType}_acknowledged"]        = $object->state->is_acknowledged ? 1 : 0;
        $obj["{$objectType}_state"]               = $object->state->$stateColumn;
        $obj["{$objectType}_last_state_change"]   = $object->state->$lastStateChangeColumn;
        $obj["{$objectType}_in_downtime"]         = $object->state->in_downtime ? 1 : 0;

        return $obj;
    }
}
