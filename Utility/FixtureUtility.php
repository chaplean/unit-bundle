<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\Driver\MySqlUtilityDriver;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Driver\PDOMySql\Driver as MySqlDriver;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;

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
     * @var array
     */
    private static $loaded;

    /**
     * @var ProxyReferenceRepository
     */
    private static $referenceRepository;

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

        $executor->setReferenceRepository(self::$referenceRepository);
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
        self::$container = ContainerUtility::getContainer($typeTest);
        /** @var Registry $registry */
        $registry = self::$container->get('doctrine');
        /** @var EntityManager $om */
        $om = $registry->getManager();

        self::$referenceRepository = new ProxyReferenceRepository($om);

        DatabaseUtility::initDatabase($typeTest, $om);
        $connection = $om->getConnection();
        $driver = $connection->getDriver();
        $name = DatabaseUtility::getParams()['dbname'];

        $executor = null;

        switch (true) {
            case $driver instanceof SqliteDriver:
                $executor = DatabaseUtility::initSqliteDatabase($classNames);
                break;
            case $driver instanceof MySqlDriver:
                $executor = DatabaseUtility::initMySqlDatabase();
                MySqlUtilityDriver::disableForeignKeyCheck($connection);
                break;
            default:
                $executor = null;
        }

        if (empty($executor)) {
            $purger = new ORMPurger();
            if (null !== $purgeMode) {
                $purger->setPurgeMode($purgeMode);
            }

            $executor = new ORMExecutor($om, $purger);

            $executor->setReferenceRepository(self::$referenceRepository);
            $executor->purge();
        }

        $loader = self::getFixtureLoader(self::$container, $classNames);
        self::$loaded = array();
        foreach ($loader->getFixtures() as $fixture) {
            self::$loaded[] = get_class($fixture);
        }

        $executor->execute($loader->getFixtures(), true);

        if (isset($name) && isset($backup)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $executor->getReferenceRepository()->save($backup);
            copy($name, $backup);
        }

        if ($driver instanceof MySqlDriver) {
            MySqlUtilityDriver::enableForeignKeyCheck($connection);
            $connection->close();
        }

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
}
