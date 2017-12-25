<?php
$section = $this->menuSection(N_('Maps'));

$section->add(N_($this->translate('Host map')), array(
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

// javascript libraries
$this->provideJsFile('vendor/spin.js');
$this->provideJsFile('vendor/leaflet.js');
$this->provideJsFile('vendor/leaflet.spin.js');
$this->provideJsFile('vendor/leaflet.markercluster.js');
$this->provideJsFile('vendor/L.Control.Locate.js');
$this->provideJsFile('vendor/easy-button.js');

// configuration menu
$this->provideConfigTab('config', array(
    'title' => $this->translate('Configure the map module'),
    'label' => $this->translate('Configuration'),
    'url' => 'config'
));
