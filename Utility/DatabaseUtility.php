<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\Driver\SqliteUtilityDriver;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * DatabaseUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class DatabaseUtility
{
    /**
     * @var array
     */
    private static $cachedMetadatas = array();

    /**
     * @var string
     */
    private static $typeTest;

    /**
     * @var EntityManager
     */
    private static $om;

    /**
     * @var Connection
     */
    private static $connection;

    /**
     * @var array
     */
    private static $params;

    /**
     * @param string   $typeTest
     * @param Registry $registry
     *
     * @return void
     */
    public static function initDatabase($typeTest, $registry)
    {
        self::$typeTest = $typeTest;
        self::$om = $registry->getManager();

        /** @var Connection $connection */
        $connection = self::$om->getConnection();
        $params = $connection->getParams();

        if (isset($params['master'])) {
            $params = $params['master'];
        }

        $name = isset($params['path']) ? $params['path'] : (isset($params['dbname']) ? $params['dbname'] : false);
        if (!$name) {
            throw new \InvalidArgumentException(
                "Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped."
            );
        }

        self::$params = $params;
    }

    /**
     * @param array $classNames
     *
     * @return \Doctrine\Common\DataFixtures\Executor\ORMExecutor
     */
    public static function initSqliteDatabase($classNames)
    {
        $container = ContainerUtility::getContainer(self::$typeTest);
        $referenceRepository = new ProxyReferenceRepository(self::$om);

        if ($container->getParameter('liip_functional_test.cache_sqlite_db')) {
            $backup = $container->getParameter('kernel.cache_dir') . '/test_' . md5(serialize(self::$cachedMetadatas['doctrine']) . serialize($classNames)) . '.db';
            if (file_exists($backup) && file_exists($backup . '.ser') && SqliteUtilityDriver::isBackupUpToDate($classNames, $backup)) {
                self::$om->flush();
                self::$om->clear();

                $executor = new ORMExecutor(self::$om);
                $executor->setReferenceRepository($referenceRepository);
                /** @var ReferenceRepository $referenceRepository */
                $referenceRepository = $executor->getReferenceRepository();
                /** @noinspection PhpUndefinedMethodInspection */
                $referenceRepository->load($backup);

                copy($backup, self::$params['path']);

                return $executor;
            }
        }

        self::createSchemaDatabase(self::$om);

        $executor = new ORMExecutor(self::$om);
        $executor->setReferenceRepository($referenceRepository);

        return $executor;
    }

    /**
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function initMySqlDatabase()
    {
        $params = self::$params;
        $name = $params['dbname'];

        unset($params['dbname']);

        $tmpConnection = DriverManager::getConnection($params);
        $shouldNotCreateDatabase = in_array($name, $tmpConnection->getSchemaManager()->listDatabases());

        if (!$shouldNotCreateDatabase) {
            $tmpConnection->getSchemaManager()->createDatabase($name);
        }

        self::createSchemaDatabase(self::$om);
    }

    /**
     * Create schema database
     *
     * @param \Doctrine\ORM\EntityManagerInterface $om
     *
     * @return void
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function createSchemaDatabase($om)
    {
        if (!isset(self::$cachedMetadatas['doctrine'])) {
            self::$cachedMetadatas['doctrine'] = $om->getMetadataFactory()->getAllMetadata();
            usort(
                self::$cachedMetadatas['doctrine'],
                function ($a, $b) {
                    return strcmp($a->name, $b->name);
                }
            );
        }
        $metadatas = self::$cachedMetadatas['doctrine'];

        $schemaTool = new SchemaTool($om);
        $schemaTool->dropDatabase();

        if (!empty($metadatas)) {
            $schemaTool->createSchema($metadatas);
        }
    }

    /**
     * Get om.
     *
     * @return EntityManager
     */
    public static function getOm()
    {
        return self::$om;
    }

    /**
     * Get connection.
     *
     * @return Connection
     */
    public static function getConnection()
    {
        return self::$connection;
    }

    /**
     * Get params.
     *
     * @return array
     */
    public static function getParams()
    {
        return self::$params;
    }
}
