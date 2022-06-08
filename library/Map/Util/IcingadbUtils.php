<?php

namespace Icinga\Module\Map\Util;

use Icinga\Module\Icingadb\Common\Auth;
use Icinga\Module\Icingadb\Common\Database;

/**
 * Class for initialising icingadb utils
 */
class IcingadbUtils
{
    use Database;
    use Auth;

    protected static $instance;

    /**
     * @see getInstance()
     */
    private function __construct()
    {
    }

    /**
     * Get the IcingadbUtils instance
     *
     * @return IcingadbUtils
     */
    public static function getInstance(): IcingadbUtils
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }
}