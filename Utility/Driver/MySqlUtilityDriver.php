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
    public static function createDatabase(Connection $connection)
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
    public static function exist(Connection $connection)
    {
        $params = $connection->getParams();
        $dbname = $params['dbname'];

        unset($params['dbname']);

        $tmpConnection = DriverManager::getConnection($params);

        return in_array($dbname, $tmpConnection->getSchemaManager()->listDatabases());
    }

    /**
     * Determine if the Fixtures that define a database backup have been
     * modified since the backup was made.
     *
     * @param array  $classNames The fixture classnames to check
     * @param string $backup     The fixture backup SQLite database file path
     *
     * @return bool TRUE if the backup was made since the modifications to the
     * fixtures; FALSE otherwise
     */
    public static function isBackupUpToDate(array $classNames, $backup)
    {
        $backupLastModifiedDateTime = new \DateTime();
        $backupLastModifiedDateTime->setTimestamp(filemtime($backup));

        foreach ($classNames as $className) {
            $fixtureLastModifiedDateTime = self::getFixtureLastModified($className);
            if ($backupLastModifiedDateTime < $fixtureLastModifiedDateTime) {
                return false;
            }
        }

        return true;
    }
}
