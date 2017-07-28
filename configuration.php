<?php
$this->menuSection('Hostmap')
	->setUrl('map')
	->setIcon('globe');

// stylesheets
$this->provideCssFile('third-party/leaflet.css');
$this->provideCssFile('third-party/MarkerCluster.css');
$this->provideCssFile('third-party/MarkerCluster.Default.css');

// javascript libraries
$this->provideJsFile('third-party/leaflet.js');
$this->provideJsFile('third-party/leaflet.markercluster.js');

// configuration menu
$this->provideConfigTab('config', array(
	'title' => 'Configure the map module',
	'label'	=> 'Configuration',
	'url'	=> 'config'
));
?>
