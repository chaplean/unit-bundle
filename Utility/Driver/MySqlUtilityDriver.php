<?php

namespace Chaplean\Bundle\UnitBundle\Utility\Driver;

use Doctrine\DBAL\Connection;

/**
 * MySqlUtilityDriver.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class MySqlUtilityDriver
{
    /**
     * @param Connection $connection
     *
     * @return void
     */
    public static function enableForeignKeyCheck($connection)
    {
        $connection->query(sprintf('SET FOREIGN_KEY_CHECKS=1'));
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    public static function disableForeignKeyCheck($connection)
    {
        $connection->query(sprintf('SET FOREIGN_KEY_CHECKS=0'));
    }
}
