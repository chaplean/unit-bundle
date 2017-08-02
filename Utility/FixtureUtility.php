<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\Mysql\MysqlDumpCommandUtility;
use Chaplean\Bundle\UnitBundle\Utility\Mysql\MysqlImportCommandUtility;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Driver\PDOMySql\Driver as MySqlDriver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * FixtureUtility.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     1.2.0
 */
class FixtureUtility
{
    /**
     * @var ORMExecutor[]
     */
    protected $cachedExecutor = array();

    /**
     * @var DatabaseUtility
     */
    private $databaseUtility;

    /**
     * @var FixtureUtility
     */
    private static $instance = null;

    /**
     * @var array
     */
    private $loaded = array();

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $namespace;

    /**
     * Singleton
     *
     * @codeCoverageIgnore
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Singleton
     *
     * FixtureUtility constructor.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Retrieve Doctrine DataFixtures loader.
     *
     * @param ContainerInterface $container
     * @param array              $classNames
     *
     * @return Loader
     */
    protected function getFixtureLoader(ContainerInterface $container, array $classNames)
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
     * Get Singleton Instance
     *
     * @return \Chaplean\Bundle\UnitBundle\Utility\FixtureUtility
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get namespace.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
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
    public function loadFixtures(array $classNames)
    {
        /** @var Registry $registry */
        $registry = $this->container->get('doctrine');
        $executor = null;

        $databaseUtility = new DatabaseUtility();
        $databaseUtility->initDatabase($classNames, $registry, $this->container);

        $driverIsMysql = $databaseUtility->getDriver() instanceof MySqlDriver;

        $databaseHash = $databaseUtility->getHash();
        $sqlDirectory = $this->container->getParameter('kernel.cache_dir') . '/sql';

        if (!@mkdir($sqlDirectory) && !is_dir($sqlDirectory)) {
            throw new FileException('Directory is not created: ' . $sqlDirectory);
        }

        if (!$databaseUtility->exist($classNames)) {
            $databaseUtility->createSchemaDatabase();
        } else {
            if (!$driverIsMysql || !array_key_exists($databaseHash, $this->cachedExecutor)) {
                $databaseUtility->cleanDatabase();
            }
        }

        if (!array_key_exists($databaseHash, $this->cachedExecutor) || $driverIsMysql) {
            if ($executor === null) {
                $connection = $databaseUtility->getOm()
                    ->getConnection();

                $referenceRepository = new ProxyReferenceRepository($databaseUtility->getOm());

                $executor = new ORMExecutor($databaseUtility->getOm(), new ORMPurger());
                $executor->setReferenceRepository($referenceRepository);

                $loader = self::getFixtureLoader($this->container, $classNames);

                $this->loaded = array();
                foreach ($loader->getFixtures() as $fixture) {
                    $this->loaded[] = get_class($fixture);
                }

                if (array_key_exists($databaseHash, $this->cachedExecutor)) {
                    if ($driverIsMysql) {
                        $mysqlImport = new MysqlImportCommandUtility($connection, $sqlDirectory . '/' . $databaseHash . '.sql');
                        $mysqlImport->exec();

                        $databaseUtility->getOm()->getUnitOfWork()->clear();
                    }

                    $executor = $this->cachedExecutor[$databaseHash];
                } else {
                    $executor->execute($loader->getFixtures());

                    $this->cachedExecutor[$databaseHash] = $executor;

                    if ($driverIsMysql) {
                        $mysqlDump = new MysqlDumpCommandUtility($connection, $sqlDirectory . '/' . $databaseHash . '.sql');
                        $mysqlDump->exec();
                    }
                }
            }
        } else {
            $executor = $this->cachedExecutor[$databaseHash];
        }

        $this->databaseUtility = $databaseUtility;

        return $executor;
    }

    /**
     * @param array                       $classNames    List of fully qualified class names of fixtures to load
     * @param EntityManager|ObjectManager $entityManager EntityManager to use
     *
     * @return ORMExecutor
     * @throws \Exception
     */
    public function loadPartialFixtures(array $classNames, $entityManager)
    {
        $executor = new ORMExecutor($entityManager);

        $loader = self::getFixtureLoader($this->container, $classNames);

        $fixtures = array();
        foreach ($loader->getFixtures() as $fixture) {
            $fixtureClass = get_class($fixture);
            if (!in_array($fixtureClass, $this->loaded)) {
                $fixtures[] = new $fixtureClass();
            }
        }

        if ($this->databaseUtility === null || !isset($this->cachedExecutor[$this->databaseUtility->getHash()])) {
            throw new \Exception('Executor cannot be found');
        }

        $executor->setReferenceRepository($this->cachedExecutor[$this->databaseUtility->getHash()]->getReferenceRepository());
        $executor->execute($fixtures, true);

        return $executor;
    }

    /**
     * Load a data fixture class.
     *
     * @param Loader $loader
     * @param string $className
     *
     * @return void
     */
    private function loadFixtureClass(Loader $loader, $className)
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
     * @return array
     */
    public function loadDefaultFixtures()
    {
        return NamespaceUtility::getClassNamesByContext($this->namespace, NamespaceUtility::DIR_DEFAULT_DATA);
    }

    /**
     * @param ContainerInterface $container
     *
     * @return FixtureUtility
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Set namespace.
     *
     * @param string $namespace
     *
     * @return FixtureUtility
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }
}
