<?php

use OpenCage\Loader\CompatLoader;

$this->provideHook('monitoring/HostActions');
$this->provideHook('monitoring/ServiceActions');
$this->provideHook('cube/Actions', 'CubeLinks');

require_once __DIR__ . '/library/vendor/OpenCage/Loader/CompatLoader.php';
CompatLoader::delegateLoadingToIcingaWeb($this->app);
