<?php

namespace Icinga\Module\Map\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Icingadb\Model\Service;
use ipl\Stdlib\Filter as IplFilter;
use Icinga\Module\Map\Web\Controller\MapController;
use Icinga\Module\Monitoring\DataView\DataView;
use OpenCage\Geocoder\Geocoder;

class SearchController extends MapController
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
        if (empty($query)) {
            return [];
        }

        if ($this->isUsingIcingadb) {
            return $this->IcingadbSearch($query, 'service');
        }

        return $this->IdoServiceSearch($query);
    }

    private function hostSearch($query)
    {
        if (empty($query)) {
            return [];
        }

        if ($this->isUsingIcingadb) {
            return $this->IcingadbSearch($query, 'host');
        }

        return $this->IdoHostSearch($query);
    }

    private function IdoServiceSearch($query)
    {
        $results = [];

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
                if (!preg_match($this->coordinatePattern, $el->coordinates)) {
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

    private function IdoHostSearch($query) {
        $results = [];

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
                if (!preg_match($this->coordinatePattern, $el->coordinates)) {
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

    private function IcingadbSearch($query, $objectType)
    {
        $results = [];

        $searchString = "*$query*";
        $query = Host::on($this->icingadbUtils->getDb());

        if ($objectType === 'service') {
            $query = Service::on($this->icingadbUtils->getDb())->with('host');
        }

        $query->filter(IplFilter::like("$objectType.vars.geolocation", '*'));
        $query->filter(IplFilter::any(
            IplFilter::like("$objectType.name", $searchString),
            IplFilter::like("$objectType.display_name", $searchString)
        ));

        $query->limit($this->limit);
        //TODO not working properly
        //$this->Filter($serviceQuery, $this->getFilter());

        $query = $query->execute();

        if ($query->hasResult()) {
            foreach ($query as $object) {
                // check for broken coordinates
                $coordinates = $object->vars['geolocation'];
                if (!preg_match($this->coordinatePattern, $coordinates)) {
                    continue;
                }

                $coordinates = explode(",", $coordinates);

                $id = $object->name;
                $name = $object->display_name;
                if ($objectType === 'service') {
                    $id = sprintf("%s!%s", $object->host->name, $object->name);
                    $name = sprintf("%s (%s)", $object->name, $object->host->name);
                }

                $results[] = [
                    "id"        => $id,
                    "name"      => $name,
                    "center"    => [
                        "lat" => $coordinates[0],
                        "lng" => $coordinates[1]
                    ],
                    "icon"      => "$objectType"
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
        $this->limit = $this->params->shift('limit', 5);
        $lite = boolval($this->params->shift('lite', 0));

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

        header('Content-Type: application/javascript; charset=utf-8');
        print $callback . "(" . json_encode($results) . ");";
        exit();
    }
}