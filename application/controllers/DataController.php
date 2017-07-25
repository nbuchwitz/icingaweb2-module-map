<?php

namespace Icinga\Module\Map\Controllers;
 
use Icinga\Module\Monitoring\Controller;
 
class DataController extends Controller
{

    public function pointsAction()
    {
        $query = $this->backend
            ->select()
            ->from('customvar', array(
                'host_name',
                'varvalue'))
            ->where('varname', 'geolocation');

        if (count($query->fetchAll()) > 0 )
        {
            $points = array();

            foreach ($query as $row)
            {
                $hostname = $row->host_name;
				$coordinates = explode(",", $row->varvalue);

                $point = array(
                    "coordinates" => $coordinates
                );

                $points[$hostname] = $point;    
            }
        }

        echo json_encode($points);
        exit;
    }

    public function indexAction()
    {
	$locations = array();
   
        $query = $this->backend
            ->select()
            ->from('customvar', array(
                'host_name',
                'varname',
		'varvalue'))
            ->where('varname', 'geolocation');

	if (count($query->fetchAll()) > 0 ) {
        	foreach ($query as $row) {
			$locationId = md5($row->varvalue);

			if(!isset($locations[$locationId])) {
				$location = array();
				// TODO: Name location
				$location['name'] = "";
				$location['coordinates'] = explode(",", $row->varvalue);
				$location['nodes'] = array();

				$locations[$locationId] = $location;
			}

			$query2 = $this->backend
            			->select()
	            		->from('servicestatus', array(
	                		'host_name',
	                		'service_display_name',
					'service_host_name',
	                		'service_state'))
		    		->where('service_host_name', $row->host_name);
	
			$this->applyRestriction('monitoring/filter/objects', $query2);

			$node = array();
			$node['services'] = array();

			foreach ($query2 as $row2) {
				$service = array("display_name" => $row2->service_display_name,
					"state" => $row2->service_state);
				array_push($node['services'], $service);
			}

			$locations[$locationId]['nodes'][$row->host_name] = $node;

        	}
		unset($query2);

        echo json_encode($locations);
	}
    
    exit;
    }
}
