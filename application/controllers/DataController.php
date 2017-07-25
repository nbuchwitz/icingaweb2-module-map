<?php

namespace Icinga\Module\Map\Controllers;
 
use Icinga\Module\Monitoring\Controller;
 
class DataController extends Controller
{

    private function hostStatus($hostname)
    {
        $status = $this->backend
            ->select()
            ->from('hoststatus', array(
                'host_name',
                'host_display_name',
                'host_acknowledged',
                'host_state',
                'host_last_state_change',
                'host_hard_state',
                'host_last_hard_state_change',
                'host_in_downtime'))
            ->where('host_name', $hostname);

        $this->applyRestriction('monitoring/filter/objects', $status);

        if ($status->count()) {
            return $status->fetchRow();
            
        } else {
            return array();
        }
    }

    private function hostData($hostname)
    {
        $host = array();
        $query = $this->backend
            ->select()
            ->from('hoststatus', array(
                'host_display_name',
                'host_acknowledged',
                'host_state',
                'host_last_state_change',
                'host_hard_state',
                'host_last_hard_state_change',
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
                'service_state',
                'service_last_state_change',
                'service_hard_state',
                'service_last_hard_state_change',
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
        $query = $this->backend
            ->select()
            ->from('customvar', array(
                'host_name',
                'varvalue'))
            ->where('varname', 'geolocation');

        $this->applyRestriction('monitoring/filter/objects', $query);

        if (count($query->fetchAll()) > 0 ) {
            $points = array();

            foreach ($query as $row) {
                $hostname = $row->host_name;
				$coordinates = explode(",", $row->varvalue);
                $host = $this->hostData($hostname);

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
