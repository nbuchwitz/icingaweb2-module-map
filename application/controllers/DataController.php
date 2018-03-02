<?php

namespace Icinga\Module\Map\Controllers;

use Dompdf\Exception;
use Icinga\Data\Filter\Filter;
use Icinga\Module\Monitoring\Controller;
use Icinga\Module\Monitoring\DataView\DataView;

class DataController extends Controller
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
        $this->setupFilterControl($dataView, null, null, ['stateType', 'objectType']);
        return $dataView;
    }

    private $stateColumn;
    private $stateChangeColumn;

    /**
     * Get JSON state objects
     */
    public function pointsAction()
    {
        $points = array();

        try {
            // Borrowed from monitoring module
            // Handle soft and hard states
            $config = $this->config();
            $stateType = strtolower($this->params->shift('stateType',
                $config->get('map', 'stateType', 'soft')
            ));

            $objectType = strtolower($this->params->shift('objectType', 'all'));

            if ($stateType === 'hard') {
                $this->stateColumn = 'hard_state';
                $this->stateChangeColumn = 'last_hard_state_change';
            } else {
                $this->stateColumn = 'state';
                $this->stateChangeColumn = 'last_state_change';
            }

            if (in_array($objectType, ['all', 'host'])) {
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

                $this->applyRestriction('monitoring/filter/objects', $serviceQuery);
                $this->filterQuery($serviceQuery);

                if ($hostQuery->count() > 0) {
                    foreach ($hostQuery as $row) {
                        $hostname = $row->host_name;

                        $host = (array)$row;
                        $host['services'] = array();

                        // check for broken coordinates
                        $coordinate_pattern = '/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/';

                        if (!preg_match($coordinate_pattern, $host['coordinates'])) {
                            continue;
                        }

                        $host['coordinates'] = explode(",", $host['coordinates']);

                        $points['hosts'][$hostname] = $host;
                    }
                }

                // add services to host
                if ($serviceQuery->count() > 0) {
                    foreach ($serviceQuery as $row) {
                        $hostname = $row->host_name;

                        $service = (array)$row;
                        unset($service['host_name']);

                        $points['hosts'][$hostname]['services'][$service['service_display_name']] = $service;
                    }
                }
            }

            if (in_array($objectType, ['all', 'service'])) {

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

                        // check for broken coordinates
                        $coordinate_pattern = '/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/';

                        if (!preg_match($coordinate_pattern, $host['coordinates'])) {
                            continue;
                        }

                        $host['coordinates'] = explode(",", $host['coordinates']);
                        $host['icon'] = $ar['icon'];
                        $points['services'][$identifier] = $host;
                    }
                }
            }
        } catch (\Exception $e) {
            $points['message'] = $e->getMessage();
            $points['trace'] = $e->getTraceAsString();
        }

        echo json_encode($points);
        exit();
    }
}
