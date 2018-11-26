<?php
$section = $this->menuSection(N_('Maps'), array('icon' => 'globe'));

$mapModule = $section->add(N_($this->translate('Default map')), array(
    'icon' => 'globe',
    'description' => $this->translate('Visualize your hosts and services on a map'),
    'url' => 'map',
    'priority' => 10
));

// stylesheets
$this->provideCssFile('vendor/leaflet.css');
$this->provideCssFile('vendor/MarkerCluster.css');
$this->provideCssFile('vendor/MarkerCluster.Default.css');
$this->provideCssFile('vendor/L.Control.Locate.css');
$this->provideCssFile('vendor/easy-button.css');
$this->provideCssFile('vendor/leaflet.awesome-markers.css');
$this->provideCssFile('vendor/leaflet.modal.css');
$this->provideCssFile('vendor/L.Control.OpenCageData.Search.min.css');

// javascript libraries
$this->provideJsFile('vendor/spin.js');
$this->provideJsFile('vendor/leaflet.js');
$this->provideJsFile('vendor/leaflet.spin.js');
$this->provideJsFile('vendor/leaflet.markercluster.js');
$this->provideJsFile('vendor/L.Control.Locate.js');
$this->provideJsFile('vendor/easy-button.js');
$this->provideJsFile('vendor/leaflet.awesome-markers.js');
$this->provideJsFile('vendor/Leaflet.Modal.js');
$this->provideJsFile('vendor/L.Control.OpenCageSearch.js');

// configuration menu
$this->provideConfigTab('config', array(
    'title' => $this->translate('Configure the map module'),
    'label' => $this->translate('Configuration'),
    'url' => 'config'
));
