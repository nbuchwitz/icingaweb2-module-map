<?php

use OpenCage\Loader\CompatLoader;

// Monitoring module hooks
$this->provideHook('monitoring/HostActions');
$this->provideHook('monitoring/ServiceActions');
// Cube module hooks
$this->provideHook('cube/Actions', 'CubeLinks');
$this->provideHook('cube/Actions', 'IcingaDbCubeLinks');
// IcingaDB module hooks
$this->provideHook('icingadb/IcingadbSupport');
$this->provideHook('icingadb/HostActions');
$this->provideHook('icingadb/ServiceActions');
// CspHook to provide a custom content-security-policy for the module
$this->provideHook('Csp');

require_once __DIR__ . '/library/vendor/OpenCage/Loader/CompatLoader.php';
CompatLoader::delegateLoadingToIcingaWeb($this->app);
