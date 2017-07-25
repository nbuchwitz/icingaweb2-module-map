<?php
$this->menuSection('Hostmap')
	->setUrl('map')
	->setIcon('globe');

//$this->provideCssFile('third-party/font-awesome/css/font-awesome.css');

$this->provideCssFile('third-party/leaflet.css');
$this->provideCssFile('third-party/MarkerCluster.css');
$this->provideCssFile('third-party/MarkerCluster.Default.css');
$this->provideJsFile('third-party/leaflet.js');
$this->provideJsFile('third-party/leaflet.markercluster.js');
$this->provideJsFile('icons.js');
?>
