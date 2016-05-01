<?php

namespace Chaplean\Bundle\UnitBundle\Utility\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;

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

    /**
     * @param Connection $connection
     *
     * @return void
     */
    public static function disableForeignKeyCheck(&$connection)
    {
        $connection->exec('SET FOREIGN_KEY_CHECKS=0');
    }

    /**
     * @param Connection $connection
     *
     * @return void
     */
    public static function enableForeignKeyCheck(&$connection)
    {
        $connection->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @param EntityManager $tmpOm
     * @param EntityManager $originalOm
     *
     * @return void
     */
    public static function moveDatabase($tmpOm, $originalOm)
    {
        $dbnameDest = $originalOm->getConnection()->getParams()['dbname'];
        $dbnameSrc = $tmpOm->getConnection()->getParams()['dbname'];

        $tables = $tmpOm->getConnection()->getSchemaManager()->listTables();
        foreach ($tables as $table) {
            $originalOm->getConnection()->exec(sprintf(
                'CREATE TABLE %s.%s SELECT * FROM %s.%s',
                $dbnameDest,
                $table->getName(),
                $dbnameSrc,
                $table->getName()
            ));
        }
    }
}
