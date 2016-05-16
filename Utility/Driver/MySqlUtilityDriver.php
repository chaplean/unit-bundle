<?php

namespace Chaplean\Bundle\UnitBundle\Utility\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

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
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function createDatabase($connection)
    {
        $params = $connection->getParams();
        $dbname = $params['dbname'];

        unset($params['dbname']);

        $tmpConnection = DriverManager::getConnection($params);
        $tmpConnection->getSchemaManager()->createDatabase($dbname);
    }

    /**
     * @param Connection $connection
     *
     * @return boolean
     */
    public static function exist($connection)
    {
        $params = $connection->getParams();
        $dbname = $params['dbname'];

        unset($params['dbname']);

        $tmpConnection = DriverManager::getConnection($params);

        return in_array($dbname, $tmpConnection->getSchemaManager()->listDatabases());
    }
}
