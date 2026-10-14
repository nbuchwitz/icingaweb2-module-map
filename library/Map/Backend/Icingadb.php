<?php

namespace Icinga\Module\Map\Backend;

use Icinga\Module\Icingadb\Common\Auth;
use Icinga\Module\Icingadb\Common\Database;

/**
 * Icingadb is used to initialize the IcingaDB backend
 */
class Icingadb
{
    use Database;
    use Auth;

    protected static ?Icingadb $instance = null;

    /**
     * @see getInstance()
     */
    private function __construct()
    {
    }

    /**
     * Get the IcingadbBackend instance
     *
     * @return Icingadb
     */
    public static function getInstance(): Icingadb
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
