<?php

namespace Icinga\Module\Map\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Module\Monitoring\Controller;
use Icinga\Module\Monitoring\DataView\DataView;
use OpenCage\Geocoder\Geocoder;

class SearchController extends Controller
{
    private $geocoder;
    private $limit;

    public function init()
    {
        parent::init();

        $apiKey = $this->config()->get("map", "opencage_apikey", "");
        if ($apiKey != "") {
            $this->geocoder = $geocoder = new Geocoder($apiKey);
        }
    }

    protected function filterQuery(DataView $dataView)
    {
        $this->setupFilterControl($dataView, null, null,
            ['stateType', 'objectType', 'problems', 'jsonp', 'q', 'proximity', 'lite']);
        return $dataView;
    }

    private function opencageSearch($query)
    {
        $results = [];

        if (!$this->geocoder) {
            return $results;
        }

        $result = $this->geocoder->geocode($query, ["limit" => $this->limit]);

        if ($result && $result['total_results'] > 0) {
            foreach ($result['results'] as $el) {
                $results[] = [
                    "name" => $el['formatted'],
                    "center" => [
                        "lat" => $el['geometry']['lat'],
                        "lng" => $el['geometry']['lng']
                    ],
                    "icon" => "globe"
                ];
            }
        }

        return $results;
    }

    private function serviceSearch($query)
    {
        $results = [];

        if (empty($query)) {
            return $results;
        }
        $filter = sprintf('(service=*%s*|service_display_name=*%s*)', $query, $query);

        $hostQuery = $this->backend
            ->select()
            ->from('servicestatus', array(
                'service_display_name',
                'service',
                'host_name',
                'coordinates' => '_service_geolocation',
            ))
            ->applyFilter(Filter::fromQueryString('_service_geolocation >'))
            ->applyFilter(Filter::fromQueryString($filter))
            ->limit($this->limit);
        $this->filterQuery($hostQuery);
        $this->applyRestriction('monitoring/filter/objects', $hostQuery);

        if ($hostQuery->count() > 0) {
            foreach ($hostQuery as $el) {
                // @TODO: Move to library
                // check for broken coordinates
                $coordinate_pattern = '/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/';

                if (!preg_match($coordinate_pattern, $el->coordinates)) {
                    continue;
                }
                $coordinates = explode(",", $el->coordinates);

                $results[] = [
                    "id" => sprintf("%s!%s", $el->host_name, $el->service),
                    "name" => sprintf("%s (%s)", $el->service, $el->host_name),
                    "center" => [
                        "lat" => $coordinates[0],
                        "lng" => $coordinates[1]
                    ],
                    "icon" => "service"
                ];
            }
        }

        return $results;
    }

    private function hostSearch($query)
    {
        $results = [];

        if (empty($query)) {
            return $results;
        }
        $filter = sprintf('(host=*%s*|host_display_name=*%s*)', $query, $query);

        $hostQuery = $this->backend
            ->select()
            ->from('hoststatus', array(
                'host_display_name',
                'host_name',
                'coordinates' => '_host_geolocation',
            ))
            ->applyFilter(Filter::fromQueryString('_host_geolocation >'))
            ->applyFilter(Filter::fromQueryString($filter))
            ->limit($this->limit);
        $this->filterQuery($hostQuery);
        $this->applyRestriction('monitoring/filter/objects', $hostQuery);

        if ($hostQuery->count() > 0) {
            foreach ($hostQuery as $el) {
                // @TODO: Move to library
                // check for broken coordinates
                $coordinate_pattern = '/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/';

                if (!preg_match($coordinate_pattern, $el->coordinates)) {
                    continue;
                }
                $coordinates = explode(",", $el->coordinates);

                $results[] = [
                    "id" => $el->host_name,
                    "name" => $el->host_display_name,
                    "center" => [
                        "lat" => $coordinates[0],
                        "lng" => $coordinates[1]
                    ],
                    "icon" => "host"
                ];
            }
        }

        return $results;
    }

    public function indexAction()
    {
        $config = $this->config();
        $query = strtolower($this->params->shift('q', ''));
        $callback = strtolower($this->params->shift('jsonp', ''));
        $this->limit = strtolower($this->params->shift('limit', 5));

        $lite = boolval(strtolower($this->params->shift('lite', 0)));

        $results = [
            "ocg" => [],
            "hosts" => [],
            "services" => []
        ];

        $results["ocg"] = $this->opencageSearch($query);
        if (!$lite) {
            $results["hosts"] = $this->hostSearch($query);
            $results["services"] = $this->serviceSearch($query);
        }
        print $callback . "(" . json_encode($results) . ");";
        exit();
    }
}