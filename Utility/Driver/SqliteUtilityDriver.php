<?php

namespace Chaplean\Bundle\UnitBundle\Utility\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

/**
 * SqliteUtilityDriver.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class SqliteUtilityDriver
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
        $dbname = $params['path'];

        unset($params['path']);

        $tmpConnection = DriverManager::getConnection($params);
        $tmpConnection->getSchemaManager()->createDatabase($dbname);
    }

    /**
     * @param Connection $connection
     * @param array      $classNames
     * @param string     $hash
     *
     * @return mixed
     */
    public static function exist($connection, $classNames = null, $hash = null)
    {
        $file = $connection->getParams()['path'];

        if ($hash !== null) {
            $file = (str_replace('.db', ('_' . $hash), $file) . '.db');
        }

        $exist = file_exists($file);
        if ($exist && $classNames != null && !self::isBackupUpToDate($classNames, $file)) {
            unlink($file);
            $exist = false;
        }

        return $exist;
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

    /**
     * This function finds the time when the data blocks of a class definition
     * file were being written to, that is, the time when the content of the
     * file was changed.
     *
     * @param string $class The fully qualified class name of the fixture class to
     *                      check modification date on.
     *
     * @return \DateTime|null
     */
    public static function getFixtureLastModified($class)
    {
        $lastModifiedDateTime = null;

        $reflClass = new \ReflectionClass($class);
        $classFileName = $reflClass->getFileName();

        if (\file_exists($classFileName)) {
            $lastModifiedDateTime = new \DateTime();
            $lastModifiedDateTime->setTimestamp(filemtime($classFileName));
        }

        return $lastModifiedDateTime;
    }

    /**
     * @param Connection $src
     * @param Connection $dest
     *
     * @return void
     */
    public static function copyDatabase($src, $dest)
    {
        $fileSrc = $src->getParams()['path'];
        $fileDest = $dest->getParams()['path'];

        copy($fileSrc, $fileDest);
    }
}
