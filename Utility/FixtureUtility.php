<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Driver\PDOMySql\Driver as MySqlDriver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * FixtureUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     1.2.0
 */
class FixtureUtility
{
    /**
     * @var DatabaseUtility
     */
    private static $database;

    /**
     * @var array
     */
    private static $loaded = array();

    /**
     * @var ORMExecutor[]
     */
    protected static $cachedExecutor = array();

    /**
     * @var Container
     */
    private static $container;

    /**
     * @var string
     */
    public static $namespace;

    /**
     * @param array                            $classNames    List of fully qualified class names of fixtures to load
     * @param EntityManager|ObjectManager|null $entityManager EntityManager to use
     *
     * @return ORMExecutor
     * @throws \Exception
     */
    public static function loadPartialFixtures(array $classNames, $entityManager)
    {
        if ($entityManager === null) {
            /** @var Registry $registry */
            $registry = self::$container->get('doctrine');
            $entityManager = $registry->getManager();
        }

        $executor = new ORMExecutor($entityManager);

        $loader = self::getFixtureLoader(self::$container, $classNames);

        $fixtures = array();
        foreach ($loader->getFixtures() as $fixture) {
            $fixtureClass = get_class($fixture);
            if (!in_array($fixtureClass, self::$loaded)) {
                $fixtures[] = new $fixtureClass();
            }
        }

        $executor->setReferenceRepository(self::$cachedExecutor[self::$database->getHash()]->getReferenceRepository());
        $executor->execute($fixtures, true);

        return $executor;
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
     * @param array $classNames List of fully qualified class names of fixtures to load
     *
     * @return ORMExecutor
     * @throws \Exception
     */
    public static function loadFixtures(array $classNames)
    {
        /** @var Registry $registry */
        $registry = self::$container->get('doctrine');
        $executor = null;

        $databaseUtility = new DatabaseUtility();
        $databaseUtility->initDatabase($classNames, $registry, self::$container);

        $driverIsMysql = $databaseUtility->getDriver() instanceof MySqlDriver;

        $databaseHash = $databaseUtility->getHash();
        $sqlDirectory = self::$container->getParameter('kernel.cache_dir') . '/sql';

        if (!$databaseUtility->exist($classNames)) {
            $databaseUtility->createSchemaDatabase();
        } else {
            if (!$driverIsMysql || !array_key_exists($databaseHash, self::$cachedExecutor)) {
                $databaseUtility->cleanDatabase();

                if (!array_key_exists($databaseHash, self::$cachedExecutor)) {
                    $databaseUtility->cleanDatabaseTemporary();
                }
            }
        }

        if (!array_key_exists($databaseHash, self::$cachedExecutor) || $driverIsMysql) {
            if ($executor === null) {
                $connection = $databaseUtility->getOm()
                    ->getConnection();

                $host = $connection->getHost();
                $port = $connection->getPort();
                $databaseName = $connection->getDatabase();
                $user = $connection->getUsername();
                $password = $connection->getPassword();

                $referenceRepository = new ProxyReferenceRepository($databaseUtility->getOm());

                $executor = new ORMExecutor($databaseUtility->getOm(), new ORMPurger());
                $executor->setReferenceRepository($referenceRepository);

                $loader = self::getFixtureLoader(self::$container, $classNames);

                self::$loaded = array();
                foreach ($loader->getFixtures() as $fixture) {
                    self::$loaded[] = get_class($fixture);
                }

                $cmdArgs = '-h' . $host . ' -P' . $port . ' -u' . $user . ' -p' . $password . ' ' . $databaseName;

                if (array_key_exists($databaseHash, self::$cachedExecutor)) {
                    if ($driverIsMysql) {
                        $mysqlCmd = 'mysql ' . $cmdArgs . ' < ' . $sqlDirectory . '/' . $databaseHash . '.sql';

                        exec($mysqlCmd, $output, $returnVar);

                        $databaseUtility->getOm()->getUnitOfWork()->clear();
                    }

                    $executor = self::$cachedExecutor[$databaseHash];
                } else {
                    $executor->execute($loader->getFixtures());

                    self::$cachedExecutor[$databaseHash] = $executor;

                    if ($driverIsMysql) {
                        if (!@mkdir($sqlDirectory) && !is_dir($sqlDirectory)) {
                            throw new FileException('Directory is not created: ' . $sqlDirectory);
                        }

                        $mysqlDumpCmd = 'mysqldump ' . $cmdArgs . ' > ' . $sqlDirectory . '/' . $databaseHash . '.sql';

                        exec($mysqlDumpCmd, $output, $returnVar);
                    }
                }
            }
        } else {
            $executor = self::$cachedExecutor[$databaseHash];
        }

        $databaseUtility->moveDatabase();
        self::$database = $databaseUtility;

        return $executor;
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
        $loaderClass = 'Symfony\Bundle\DoctrineFixturesBundle\Common\DataFixtures\Loader';

        if (class_exists('Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader')) {
            $loaderClass = 'Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader';
        } elseif (class_exists('Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader')) {
            $loaderClass = 'Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader';
        }

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

    /**
     * @param string|null $namespace
     *
     * @return array
     */
    public static function loadDefaultFixtures($namespace = null)
    {
        $namespaceBackup = null;

        if ($namespace !== null) {
            $namespaceBackup = self::$namespace;
            self::$namespace = $namespace;
        }

        $dataFixtures = NamespaceUtility::getClassNamesByContext(self::$namespace, NamespaceUtility::DIR_DEFAULT_DATA);

        if ($namespaceBackup !== null) {
            self::$namespace = $namespaceBackup;
        }

        return $dataFixtures;
    }

    /**
     * @param Container|ContainerInterface $container
     *
     * @return void
     */
    public static function setContainer($container)
    {
        self::$container = $container;
    }
}
