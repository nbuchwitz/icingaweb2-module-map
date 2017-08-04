<?php
$this->menuSection('Host Map')
	->setUrl('map')
	->setIcon('globe');

// stylesheets
$this->provideCssFile('third-party/leaflet.css');
$this->provideCssFile('third-party/MarkerCluster.css');
$this->provideCssFile('third-party/MarkerCluster.Default.css');
$this->provideCssFile('third-party/L.Control.Locate.min.css');

// javascript libraries
$this->provideJsFile('third-party/leaflet.js');
$this->provideJsFile('third-party/leaflet.markercluster.js');
$this->provideJsFile('third-party/L.Control.Locate.min.js');

// configuration menu
$this->provideConfigTab('config', array(
	'title' => 'Configure the map module',
	'label'	=> 'Configuration',
	'url'	=> 'config'
));
?>
