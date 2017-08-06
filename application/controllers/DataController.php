<?php

namespace Icinga\Module\Map\Controllers;
 
use Icinga\Module\Monitoring\Controller;
 
class DataController extends Controller
{

        private $stateColumn;
        private $stateChangeColumn;

    private function hostData($hostname)
    {
        $host = array();
        $query = $this->backend
            ->select()
            ->from('hoststatus', array(
                'host_display_name',
                'host_icon_image',
                'host_icon_image_alt',
                'host_acknowledged',
                'host_state' => $this->stateColumn,
                'host_last_state_change' => $this->stateChangeColumn,
                'host_in_downtime'))
            ->where('host_name', $hostname);

        $this->applyRestriction('monitoring/filter/objects', $query);

        if ($query->count()) {
            $row = $query->fetchRow();

            $host = json_decode(json_encode($row), True);
        }

        unset($query);

        return $host;
    }

    private function hostServiceData($hostname)
    {
        $services = array();
        $query = $this->backend
            ->select()
            ->from('servicestatus', array(
                'service_display_name',
                'service_acknowledged',
                'service_state' => $this->stateColumn,
                'service_last_state_change' => $this->stateChangeColumn,
                'service_in_downtime'))
            ->where('host_name', $hostname);

        $this->applyRestriction('monitoring/filter/objects', $query);

        if ($query->count()) {
            foreach ($query as $row) {
                $service_display_name = $row->service_display_name;
                $service = json_decode(json_encode($row), True);

                $services[$service_display_name] = $service;
            }
        }

        unset($query);

        return $services;
    }

    public function pointsAction()
    {
        # borrowed from monitoring module
        # Handle soft and hard states
        $config = $this->config();
        $stateType = strtolower($this->params->shift('stateType',
            $config->get('map', 'stateType', 'soft')
        ));

        if ($stateType === 'hard') {
            $this->stateColumn = 'host_hard_state';
            $this->stateChangeColumn = 'host_last_hard_state_change';
        } else {
            $this->stateColumn = 'host_state';
            $this->stateChangeColumn = 'host_last_state_change';
        }

        $query = $this->backend
            ->select()
            ->from('customvar', array(
                'host_name',
                'varvalue'))
            ->where('varname', 'geolocation');

        if (count($query->fetchAll()) > 0 ) {
            $points = array();

            foreach ($query as $row) {
                $hostname = $row->host_name;
                $coordinates = explode(",", $row->varvalue);
                $host = $this->hostData($hostname);
        
                # skip this host, if the user lacks sufficient permission to fetch host data
                if(empty($host)) {
                    continue;
                }

                $point = array_merge(
                    array(
                        "coordinates" => $coordinates,
                        "services"    => $this->hostServiceData($hostname)
                    ), $host
                );

                $points[$hostname] = $point;    
            }
        }

        echo json_encode($points);
        exit;
    }
}
