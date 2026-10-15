<?php

namespace Icinga\Module\Map\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Icingadb\Model\Service;
use Icinga\Module\Map\Web\Controller\MapController;
use Icinga\Module\Monitoring\DataView\DataView;

use ipl\Stdlib\Filter as IplFilter;

use OpenCage\Geocoder\Geocoder;

class SearchController extends MapController
{
    /** @var ?Geocoder The OpenCage Geocoder that can be used for the search */
    private ?Geocoder $geocoder = null;

    /** @var int Limit for the search results */
    private int $limit;

    public function init()
    {
        parent::init();

        $apiKey = $this->config()->get("map", "opencage_apikey", "");
        if ($apiKey != "") {
            $this->geocoder = $geocoder = new Geocoder($apiKey);
        }
    }

    protected function filterQuery(DataView $dataView): DataView
    {
        $this->setupFilterControl(
            $dataView,
            null,
            null,
            ['stateType', 'objectType', 'problems', 'jsonp', 'q', 'proximity', 'lite']
        );
        return $dataView;
    }

    private function opencageSearch(string $query): array
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

    private function serviceSearch(string $query): array
    {
        if (empty($query)) {
            return [];
        }

        if ($this->isUsingIcingadb) {
            return $this->icingadbSearch($query, 'service');
        }

        return $this->idoServiceSearch($query);
    }

    private function hostSearch(string $query): array
    {
        if (empty($query)) {
            return [];
        }

        if ($this->isUsingIcingadb) {
            return $this->icingadbSearch($query, 'host');
        }

        return $this->idoHostSearch($query);
    }

    private function idoServiceSearch(string $query): array
    {
        $results = [];

        $filter = sprintf('(service=*%s*|service_display_name=*%s*)', $query, $query);

        $hostQuery = $this->monitoringBackend->getDb()
            ->select()
            ->from('servicestatus', [
                'service_display_name',
                'service',
                'host_name',
                'coordinates' => '_service_geolocation',
            ])
            ->applyFilter(Filter::fromQueryString('_service_geolocation >'))
            ->applyFilter(Filter::fromQueryString($filter))
            ->limit($this->limit);
        $this->filterQuery($hostQuery);
        $this->monitoringBackend->applyRestriction('monitoring/filter/objects', $hostQuery);

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

    private function idoHostSearch(string $query): array
    {
        $results = [];

        $filter = sprintf('(host=*%s*|host_display_name=*%s*)', $query, $query);

        $hostQuery = $this->monitoringBackend->getDb()
            ->select()
            ->from('hoststatus', [
                'host_display_name',
                'host_name',
                'coordinates' => '_host_geolocation',
            ])
            ->applyFilter(Filter::fromQueryString('_host_geolocation >'))
            ->applyFilter(Filter::fromQueryString($filter))
            ->limit($this->limit);

        $this->filterQuery($hostQuery);
        $this->monitoringBackend->applyRestriction('monitoring/filter/objects', $hostQuery);

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

    private function icingadbSearch(string $query, string $objectType): array
    {
        $results = [];

        $searchString = "*$query*";
        $query = Host::on($this->icingadbBackend->getDb());

        if ($objectType === 'service') {
            $query = Service::on($this->icingadbBackend->getDb())->with('host');
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
                    "id" => $id,
                    "name" => $name,
                    "center" => [
                        "lat" => $coordinates[0],
                        "lng" => $coordinates[1]
                    ],
                    "icon" => "$objectType"
                ];
            }
        }

        return $results;
    }

    /**
     * indexAction implements a JSONP search endpoint that returns OpenCage geocoding,
     * host and service results for the given query.
     **/
    public function indexAction()
    {
        $config = $this->config();
        $query = strtolower($this->params->shift('q', ''));
        $callback = strtolower($this->params->shift('jsonp', ''));
        $this->limit = intval($this->params->shift('limit', 5));
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
