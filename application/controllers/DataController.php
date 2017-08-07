<?php

namespace Icinga\Module\Map\Controllers;
 
use Icinga\Module\Monitoring\Controller;

class DataController extends Controller
{

    private $stateColumn;
    private $stateChangeColumn;

    /**
     * Fetch host state data for given hostname
     *
     * Method returns empty array if hostname is not found or user lacks permissions
     *
     * @param string $hostname
     * @return array host state data
     */
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
                'host_state' => 'host_' . $this->stateColumn,
                'host_last_state_change' => 'host_' . $this->stateChangeColumn,
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

    /**
     * Fetch services states for given hostname
     *
     * Method returns empty array if hostname is not found or user lacks permissions
     *
     * @param $hostname
     * @return array service states
     */
    private function hostServiceData($hostname)
    {
        $services = array();
        $query = $this->backend
            ->select()
            ->from('servicestatus', array(
                'service_display_name',
                'service_acknowledged',
                'service_state' => 'service_' . $this->stateColumn,
                'service_last_state_change' => 'service_' . $this->stateChangeColumn,
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


    /**
     * Get JSON state objects
     */
    public function pointsAction()
    {
        // Borrowed from monitoring module
        // Handle soft and hard states
        $config = $this->config();
        $stateType = strtolower($this->params->shift('stateType',
            $config->get('map', 'stateType', 'soft')
        ));

        if ($stateType === 'hard') {
            $this->stateColumn = 'hard_state';
            $this->stateChangeColumn = 'last_hard_state_change';
        } else {
            $this->stateColumn = 'state';
            $this->stateChangeColumn = 'last_state_change';
        }

        $query = $this->backend
            ->select()
            ->from('customvar', array(
                'host_name',
                'varvalue'))
            ->where('varname', 'geolocation');

        $points = array();

        if (count($query->fetchAll()) > 0) {
            foreach ($query as $row) {
                $hostname = $row->host_name;
                $coordinates = explode(",", $row->varvalue);
                $host = $this->hostData($hostname);

                // skip this host, if the user lacks sufficient permission to fetch host data
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
