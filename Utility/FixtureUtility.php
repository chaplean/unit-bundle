<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
     * @var array
     */
    private static $cachedMetadatas = array();

    /**
     * @var array
     */
    private $excludedDoctrineTables = array();

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
        $container = $this->container;
        /** @var ManagerRegistry $registry */
        $registry = $container->get('doctrine');
        /** @var ObjectManager $om */
        $om = $registry->getManager();
        $type = $registry->getName();

        $executorClass = 'PHPCR' === $type && class_exists(
            'Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor'
        ) ? 'Doctrine\Bundle\PHPCRBundle\DataFixtures\PHPCRExecutor' : 'Doctrine\\Common\\DataFixtures\\Executor\\' . $type . 'Executor';
        $referenceRepository = new ProxyReferenceRepository($om);
        $cacheDriver = $om->getMetadataFactory()->getCacheDriver();

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }

        if ('ORM' === $type) {
            $connection = $om->getConnection();
            if ($connection->getDriver() instanceof SqliteDriver) {
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

                if (!isset(self::$cachedMetadatas['default'])) {
                    self::$cachedMetadatas['default'] = $om->getMetadataFactory()->getAllMetadata();
                    usort(
                        self::$cachedMetadatas['default'],
                        function ($a, $b) {
                            return strcmp($a->name, $b->name);
                        }
                    );
                }
                $metadatas = self::$cachedMetadatas['default'];

//                if ($container->getParameter('liip_functional_test.cache_sqlite_db')) {
                $backup = $container->getParameter('kernel.cache_dir') . '/test_' . md5(serialize($metadatas) . serialize($classNames)) . '.db';

                if (file_exists($backup) && file_exists($backup . '.ser') && $this->isBackupUpToDate($classNames, $backup)) {
                    $connection = $container->get('doctrine.orm.entity_manager')->getConnection();
                    if (null !== $connection) {
                        $connection->close();
                    }

                    $om->flush();
                    $om->clear();

                    $this->preFixtureBackupRestore($om, $referenceRepository, $backup);

                    copy($backup, $name);

                    $executor = new $executorClass($om);
                    $executor->setReferenceRepository($referenceRepository);
                    $executor->getReferenceRepository()->load($backup);

                    $this->postFixtureBackupRestore($backup);

                    return $executor;
                }
//                }

                // TODO: handle case when using persistent connections. Fail loudly?
                $schemaTool = new SchemaTool($om);
                $schemaTool->dropDatabase();
                if (!empty($metadatas)) {
                    $schemaTool->createSchema($metadatas);
                }
                $this->postFixtureSetup();

                $executor = new $executorClass($om);
                $executor->setReferenceRepository($referenceRepository);
            }
        }

        if (empty($executor)) {
            $purgerClass = 'Doctrine\\Common\\DataFixtures\\Purger\\' . $type . 'Purger';
            if ('PHPCR' === $type) {
                $purger = new $purgerClass($om);
                $initManager = $container->has('doctrine_phpcr.initializer_manager') ? $container->get(
                    'doctrine_phpcr.initializer_manager'
                ) : null;

                $executor = new $executorClass($om, $purger, $initManager);
            } else {
                if ('ORM' === $type) {
                    $purger = new $purgerClass(null, $this->excludedDoctrineTables);
                } else {
                    $purger = new $purgerClass();
                }

//                if (null !== $purgeMode) {
//                    $purger->setPurgeMode($purgeMode);
//                }

                $executor = new $executorClass($om, $purger);
            }

            $executor->setReferenceRepository($referenceRepository);
            $executor->purge();
        }

        $loader = $this->getFixtureLoader($container, $classNames);

        $executor->execute($loader->getFixtures(), true);

        if (isset($name) && isset($backup)) {
            $this->preReferenceSave($om, $executor, $backup);

            $executor->getReferenceRepository()->save($backup);
            copy($name, $backup);

            $this->postReferenceSave($om, $executor, $backup);
        }

        return $executor;
    }

    /**
     * This function finds the time when the data blocks of a class definition
     * file were being written to, that is, the time when the content of the
     * file was changed.
     *
     * @param string $class The fully qualified class name of the fixture class to
     *                      check modification date on
     *
     * @return \DateTime|null
     */
    protected function getFixtureLastModified($class)
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

    /**
     * Callback function to be executed after Schema creation.
     * Use this to execute acl:init or other things necessary.
     */
    protected function postFixtureSetup()
    {
    }

    /**
     * Callback function to be executed after Schema restore.
     *
     * @return self
     *
     * @deprecated since version 1.8, to be removed in 2.0. Use postFixtureBackupRestore method instead.
     */
    protected function postFixtureRestore()
    {
    }

    /**
     * Callback function to be executed before Schema restore.
     *
     * @param ObjectManager            $manager             The object manager
     * @param ProxyReferenceRepository $referenceRepository The reference repository
     *
     * @return self
     *
     * @deprecated since version 1.8, to be removed in 2.0. Use preFixtureBackupRestore method instead.
     */
    protected function preFixtureRestore(ObjectManager $manager, ProxyReferenceRepository $referenceRepository)
    {
    }

    /**
     * Callback function to be executed after Schema restore.
     *
     * @param string $backupFilePath Path of file used to backup the references of the data fixtures
     *
     * @return self
     */
    protected function postFixtureBackupRestore($backupFilePath)
    {
        $this->postFixtureRestore();

        return $this;
    }

    /**
     * Callback function to be executed before Schema restore.
     *
     * @param ObjectManager            $manager             The object manager
     * @param ProxyReferenceRepository $referenceRepository The reference repository
     * @param string                   $backupFilePath      Path of file used to backup the references of the data fixtures
     *
     * @return self
     */
    protected function preFixtureBackupRestore(ObjectManager $manager, ProxyReferenceRepository $referenceRepository, $backupFilePath)
    {
        $this->preFixtureRestore($manager, $referenceRepository);

        return $this;
    }

    /**
     * Callback function to be executed after save of references.
     *
     * @param ObjectManager    $manager        The object manager
     * @param AbstractExecutor $executor       Executor of the data fixtures
     * @param string           $backupFilePath Path of file used to backup the references of the data fixtures
     *
     * @return self
     */
    protected function postReferenceSave(ObjectManager $manager, AbstractExecutor $executor, $backupFilePath)
    {
    }

    /**
     * Callback function to be executed before save of references.
     *
     * @param ObjectManager    $manager        The object manager
     * @param AbstractExecutor $executor       Executor of the data fixtures
     * @param string           $backupFilePath Path of file used to backup the references of the data fixtures
     *
     * @return self
     */
    protected function preReferenceSave(ObjectManager $manager, AbstractExecutor $executor, $backupFilePath)
    {
    }

    /**
     * Determine if the Fixtures that define a database backup have been
     * modified since the backup was made.
     *
     * @param array  $classNames The fixture classnames to check
     * @param string $backup     The fixture backup SQLite database file path
     *
     * @return bool TRUE if the backup was made since the modifications to the
     *              fixtures; FALSE otherwise
     */
    protected function isBackupUpToDate(array $classNames, $backup)
    {
        $nowPlus1Hour = new \DateTime('+1 hour');

        if (filemtime($backup) < $nowPlus1Hour->getTimestamp()) {
            return false;
        }


        $backupLastModifiedDateTime = new \DateTime();
        $backupLastModifiedDateTime->setTimestamp(filemtime($backup));

        /** @var \Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader $loader */
        $loader = $this->getFixtureLoader($this->container, $classNames);

        // Use loader in order to fetch all the dependencies fixtures.
        foreach ($loader->getFixtures() as $className) {
            $fixtureLastModifiedDateTime = $this->getFixtureLastModified($className);
            if ($backupLastModifiedDateTime < $fixtureLastModifiedDateTime) {
                return false;
            }
        }

        return true;
    }
}
