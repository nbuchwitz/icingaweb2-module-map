<?php

namespace Icinga\Module\Map\Controllers;

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
        $this->setupFilterControl($dataView, null, null, array(
            'format', // handleFormatRequest()
            'stateType', // hostsAction() and servicesAction()
            'addColumns', // addColumns()
            'problems' // servicegridAction()
        ));
        $this->handleFormatRequest($dataView);
        return $dataView;
    }

    /**
     * Get columns to be added from URL parameter 'addColumns'
     * and assign to $this->view->addColumns (as array)
     *
     * @return array
     */
    protected function addColumns()
    {
        $columns = preg_split(
            '~,~',
            $this->params->shift('addColumns', ''),
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        $this->view->addColumns = $columns;
        return $columns;
    }

    private $stateColumn;
    private $stateChangeColumn;

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
            ->from('servicestatus', array(
                'host_display_name',
                'host_name',
                'host_acknowledged',
                'host_state' => 'host_' . $this->stateColumn,
                'host_last_state_change' => 'host_' . $this->stateChangeColumn,
                'host_in_downtime',
                'coordinates' => '_host_geolocation',
                'service_display_name',
                'service_acknowledged',
                'service_state' => 'service_' . $this->stateColumn,
                'service_last_state_change' => 'service_' . $this->stateChangeColumn,
                'service_in_downtime'
            ))
            ->applyFilter(Filter::fromQueryString('_host_geolocation >'));

        $this->applyRestriction('monitoring/filter/objects', $query);
        $this->filterQuery($query);

        $points = array();

        if ($query->count() > 0) {
            //print_r($query->fetchAll());
            foreach ($query as $row) {
                $hostname = $row->host_name;
                $data = json_decode(json_encode($row), true);

                if (!array_key_exists($hostname, $points)) {
                    $host = $this->filterArray($data, "^host");
                    $host['services'] = array();
                    $host['coordinates'] = explode(",", $row->coordinates);

                    $points[$hostname] = $host;
                }

                $service = $this->filterArray($data, "^service");

                $points[$hostname]['services'][$service['service_display_name']] = $service;
            }
        }

        echo json_encode($points);
        exit;
    }

    function filterArray($haystack, $needle)
    {
        $matches = preg_grep("/$needle/", array_keys($haystack));

        return array_intersect_key($haystack, array_flip($matches));
    }
}
