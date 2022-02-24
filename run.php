<?php

use OpenCage\Loader\CompatLoader;

$this->provideHook('monitoring/HostActions');
$this->provideHook('monitoring/ServiceActions');
$this->provideHook('cube/Actions', 'CubeLinks');
$this->provideHook('icingadb/IcingadbSupport');
$this->provideHook('icingadb/HostActions');
$this->provideHook('icingadb/ServiceActions');

require_once __DIR__ . '/library/vendor/OpenCage/Loader/CompatLoader.php';
CompatLoader::delegateLoadingToIcingaWeb($this->app);
