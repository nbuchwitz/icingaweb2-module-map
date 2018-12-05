<?php

namespace OpenCage\Loader;

use Icinga\Application\ApplicationBootstrap;

class CompatLoader
{
    public static function delegateLoadingToIcingaWeb(ApplicationBootstrap $app)
    {
        $app->getLoader()->registerNamespace(
            'OpenCage',
            dirname(__DIR__)
        );
    }
}
