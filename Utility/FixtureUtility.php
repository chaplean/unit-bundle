<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Driver\PDOMySql\Driver as MySqlDriver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    private static $loaded;
    
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
     * @param array              $classNames    List of fully qualified class names of fixtures to load
     * @param EntityManager|null $entityManager EntityManager to use
     *
     * @return ORMExecutor
     */
    public static function loadPartialFixtures(array $classNames, $entityManager)
    {
        if (empty($entityManager)) {
            /** @var Registry $registry */
            $registry = self::$container->get('doctrine');
            /** @var EntityManager $om */
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
     * @param array          $classNames List of fully qualified class names of fixtures to load
     * @param string         $typeTest   Name of type test (logical, functional or behat)
     * @param integer|string $purgeMode  Sets the ORM purge mode
     *
     * @return ORMExecutor
     */
    public static function loadFixtures(array $classNames, $typeTest, $purgeMode = ORMPurger::PURGE_MODE_DELETE)
    {
        unset($purgeMode);
        /** @var Registry $registry */
        $registry = self::$container->get('doctrine');
        $executor = null;

        $databaseUtility = new DatabaseUtility();
        $databaseUtility->initDatabase($classNames, $typeTest, $registry, self::$container);

        if (!$databaseUtility->exist()) {
            $databaseUtility->createSchemaDatabase();
        } else {
            $databaseUtility->cleanDatabase();

            if (!isset(self::$cachedExecutor[$databaseUtility->getHash()])) {
                $databaseUtility->cleanDatabaseTemporary();
            }
        }

        if (!isset(self::$cachedExecutor[$databaseUtility->getHash()]) || $databaseUtility->getDriver() instanceof MySqlDriver) {
            if (empty($executor)) {
                $referenceRepository = new ProxyReferenceRepository($databaseUtility->getOm());

                $executor = new ORMExecutor($databaseUtility->getOm(), new ORMPurger());
                $executor->setReferenceRepository($referenceRepository);

                $loader = self::getFixtureLoader(self::$container, $classNames);

                self::$loaded = array();
                foreach ($loader->getFixtures() as $fixture) {
                    self::$loaded[] = get_class($fixture);
                }

                $executor->execute($loader->getFixtures());

                self::$cachedExecutor[$databaseUtility->getHash()] = $executor;
            }
        } else {
            $executor = self::$cachedExecutor[$databaseUtility->getHash()];
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

    /**
     * @param string|null $namespace
     *
     * @return array
     */
    public static function loadDefaultFixtures($namespace = null)
    {
        if (!empty($namespace)) {
            $namespaceBckup = self::$namespace;
            self::$namespace = $namespace;
        }

        $dataFixtures = NamespaceUtility::getClassNamesByContext(self::$namespace, NamespaceUtility::DIR_DEFAULT_DATA);

        if (isset($namespaceBckup)) {
            self::$namespace = $namespaceBckup;
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
