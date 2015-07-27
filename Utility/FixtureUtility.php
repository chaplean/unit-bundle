<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\SchemaTool;
use MyProject\Proxies\__CG__\stdClass;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\DBAL\Driver\PDOMySql\Driver as MySqlDriver;

/**
 * FixtureUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     1.2.0
 */
class FixtureUtility
{
    const BEHAT_KERNEL = '\BehatKernel';
    const DEFAULT_KERNEL = '\AppKernel';

    /**
     * @var array
     */
    private static $cachedMetadatas = array();

    /**
     * @var Container
     */
    private static $container;


    /**
     * Load container
     *
     * @param string $typeTest Define a type of test (logical, functionnal or behat)
     *
     * @return void
     */
    private static function loadContainer($typeTest)
    {
        switch ($typeTest) {
            case 'behat':
                require_once 'vendor/chaplean/unit-bundle/Chaplean/Bundle/UnitBundle/BehatKernel.php';
                $kernelClass = FixtureUtility::BEHAT_KERNEL;
                $kernel = new $kernelClass('behat', true);
                break;

            case 'functional':
            case 'logical':
            default:
//                require_once 'app/AppKernel.php';
                $kernelClass = FixtureUtility::DEFAULT_KERNEL;
                $kernel = new $kernelClass('test', true);
                break;
        }

        $kernel->boot();

        self::$container = $kernel->getContainer();
    }

    /**
     * Set the database to the provided fixtures.
     *
     * Drops the current database and then loads fixtures using the specified
     * classes. The parameter is a list of fully qualified class names of
     * classes that implement Doctrine\Common\DataFixtures\FixtureInterface
     * so that they can be loaded by the DataFixtures Loader::addFixture
     *
     * When using SQLite this method will automatically make a copy of the
     * loaded schema and fixtures which will be restored automatically in
     * case the same fixture classes are to be loaded again. Caveat: changes
     * to references and/or identities may go undetected.
     *
     * Depends on the doctrine data-fixtures library being available in the
     * class path.
     *
     * @param array   $classNames   List of fully qualified class names of fixtures to load
     * @param string  $typeTest     Name of type test (logical, functional or behat)
     * @param string  $omName       The name of object manager to use
     * @param string  $registryName The service id of manager registry to use
     * @param integer $purgeMode    Sets the ORM purge mode
     *
     * @return ORMExecutor
     */
    public static function loadFixtures(array $classNames, $typeTest, $omName = null, $registryName = 'doctrine', $purgeMode = ORMPurger::PURGE_MODE_DELETE)
    {
        self::loadContainer($typeTest);
        $registry = self::$container->get($registryName);

        if ($registry instanceof ManagerRegistry) {
            $om = $registry->getManager($omName);
            $type = $registry->getName();
        } else {
            $om = $registry->getEntityManager($omName);
            $type = 'ORM';
        }

        $executorClass = 'PHPCR' === $type && class_exists(
            'Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor'
        ) ? 'Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor' : 'Doctrine\\Common\\DataFixtures\\Executor\\' . $type . 'Executor';
        $referenceRepository = new ProxyReferenceRepository($om);
        $cacheDriver = $om->getMetadataFactory()->getCacheDriver();

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }

        $connection = $om->getConnection();
        $driver = $connection->getDriver();
        if ('ORM' === $type) {
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

            if ($driver instanceof SqliteDriver) {
                if (self::$container->getParameter('liip_functional_test.cache_sqlite_db')) {
                    $backup = self::$container->getParameter('kernel.cache_dir') . '/test_' . md5(serialize(self::$cachedMetadatas[$omName]) . serialize($classNames)) . '.db';
                    if (file_exists($backup) && file_exists($backup . '.ser') && self::isBackupUpToDate($classNames, $backup)) {
                        $om->flush();
                        $om->clear();

                        $executor = new $executorClass($om);
                        $executor->setReferenceRepository($referenceRepository);
                        $executor->getReferenceRepository()->load($backup);

                        copy($backup, $name);

                        return $executor;
                    }
                }

                self::createSchemaDatabase($omName, $om, $name);

                $executor = new $executorClass($om);
                $executor->setReferenceRepository($referenceRepository);
            } elseif ($driver instanceof MySqlDriver) {
                unset($params['dbname']);

//                if ($params['port'] != '8889') {
//                    throw new Exception('Port invalid require: 8889, actual: ' . $params['port']);
//                }

                $tmpConnection = DriverManager::getConnection($params);
                $shouldNotCreateDatabase = in_array($name, $tmpConnection->getSchemaManager()->listDatabases());

                if (!$shouldNotCreateDatabase) {
                    $tmpConnection->getSchemaManager()->createDatabase($name);
                }

                self::createSchemaDatabase($omName, $om, $name);

                $connection->query(sprintf('SET FOREIGN_KEY_CHECKS=0'));
            }
        }

        if (empty($executor)) {
            $purgerClass = 'Doctrine\\Common\\DataFixtures\\Purger\\' . $type . 'Purger';
            if ('PHPCR' === $type) {
                $purger = new $purgerClass($om);
                $initManager = self::$container->has('doctrine_phpcr.initializer_manager') ? self::$container->get(
                    'doctrine_phpcr.initializer_manager'
                ) : null;

                $executor = new $executorClass($om, $purger, $initManager);
            } else {
                $purger = new $purgerClass();
                if (null !== $purgeMode) {
                    $purger->setPurgeMode($purgeMode);
                }

                $executor = new $executorClass($om, $purger);
            }

            $executor->setReferenceRepository($referenceRepository);
            $executor->purge();
        }

        $loader = self::getFixtureLoader(self::$container, $classNames);

        $executor->execute($loader->getFixtures(), true);

        if (isset($name) && isset($backup)) {
            $executor->getReferenceRepository()->save($backup);
            copy($name, $backup);
        }

        if ($driver instanceof MySqlDriver) {
            // re enable check foreign key
            $connection->query(sprintf('SET FOREIGN_KEY_CHECKS=0'));
            $connection->close();
        }

        return $executor;
    }

    /**
     * Create schema database
     *
     * @param string        $omName
     * @param ObjectManager $om
     * @param string        $name
     *
     * @return void
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    private static function createSchemaDatabase($omName, $om, $name)
    {
        if (!isset(self::$cachedMetadatas[$omName])) {
            self::$cachedMetadatas[$omName] = $om->getMetadataFactory()->getAllMetadata();
            usort(
                self::$cachedMetadatas[$omName],
                function ($a, $b) {
                    return strcmp($a->name, $b->name);
                }
            );
        }
        $metadatas = self::$cachedMetadatas[$omName];

        $schemaTool = new SchemaTool($om);
        $schemaTool->dropDatabase($name);
        if (!empty($metadatas)) {
            $schemaTool->createSchema($metadatas);
        }
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
    private static function isBackupUpToDate(array $classNames, $backup)
    {
        $backupLastModifiedDateTime = new \DateTime();
        $backupLastModifiedDateTime->setTimestamp(filemtime($backup));

        foreach ($classNames as &$className) {
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
    private static function getFixtureLastModified($class)
    {
        $lastModifiedDateTime = null;

        $reflClass = new \ReflectionClass($class);
        $classFileName = $reflClass->getFileName();

        if (file_exists($classFileName)) {
            $lastModifiedDateTime = new \DateTime();
            $lastModifiedDateTime->setTimestamp(filemtime($classFileName));
        }

        return $lastModifiedDateTime;
    }

    /**
     * Retrieve Doctrine DataFixtures loader.
     *
     * @param Container $container
     * @param array     $classNames
     *
     * @return Loader
     */
    protected static function getFixtureLoader(Container $container, array $classNames)
    {
        $loaderClass = class_exists(
            'Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader'
        ) ? 'Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader' : (class_exists(
            'Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader'
        ) ? 'Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader' : 'Symfony\Bundle\DoctrineFixturesBundle\Common\DataFixtures\Loader');

        $loader = new $loaderClass($container);

        foreach ($classNames as $className) {
            self::loadFixtureClass($loader, $className);
        }

        return $loader;
    }

    /**
     * Load a data fixture class.
     *
     * @param Loader $loader
     * @param string $className
     *
     * @return void
     */
    private static function loadFixtureClass($loader, $className)
    {
        $fixture = new $className();

        if ($loader->hasFixture($fixture)) {
            unset($fixture);

            return;
        }

        $loader->addFixture($fixture);

        if ($fixture instanceof DependentFixtureInterface) {
            foreach ($fixture->getDependencies() as $dependency) {
                self::loadFixtureClass($loader, $dependency);
            }
        }
    }
}
