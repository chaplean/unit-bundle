<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * self.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     1.2.0
 */
class FixtureLiteUtility
{
    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private static $cachedMetadatas = [];

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
     * self constructor.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
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

    public function getHash(array $classNames)
    {
        return md5(serialize(self::$cachedMetadatas['default']) . serialize($classNames) . date('YMDH'));
    }

    /**
     * Get Singleton Instance
     *
     * @param ContainerInterface $container
     *
     * @return self
     */
    public static function getInstance(ContainerInterface $container)
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->setContainer($container);
        }

        return self::$instance;
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
     * @param array   $classNames List of fully qualified class names of fixtures to load
     * @param boolean $append     Append or not $classNames in database
     *
     * @return ORMExecutor
     * @throws \Exception
     */
    public function loadFixtures(array $classNames, bool $append = true)
    {
        $container = $this->container;
        /** @var ManagerRegistry $registry */
        $registry = $container->get('doctrine');
        /** @var EntityManager $om */
        $om = $registry->getManager();

        $referenceRepository = new ProxyReferenceRepository($om);
        /** @var CacheProvider $cacheDriver */
        $cacheDriver = $om->getMetadataFactory()->getCacheDriver();

        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }

        $connection = $om->getConnection();
        if ($connection->getDriver() instanceof SqliteDriver) {
            $params = $connection->getParams();
            if (isset($params['master'])) {
                $params = $params['master'];
            }

            $name = isset($params['path']) ? $params['path'] : false;
            if (!$name) {
                throw new \InvalidArgumentException(
                    "Connection does not contain a 'path' parameter"
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

            if ($container->getParameter('liip_functional_test.cache_sqlite_db')) {
                $backup = $container->getParameter('kernel.cache_dir') . '/test_' . $this->getHash($classNames) . '.db';

                if (file_exists($backup) && file_exists($backup . '.ser') && $this->isBackupUpToDate($classNames, $backup)) {
                    /** @var Connection $connection */
                    $connection = $container->get('doctrine.orm.entity_manager')->getConnection();

                    if (null !== $connection) {
                        $connection->close();
                    }

                    $om->flush();
                    $om->clear();

                    copy($backup, $name);

                    $executor = new ORMExecutor($om, new ORMPurger());
                    $executor->setReferenceRepository($referenceRepository);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $executor->getReferenceRepository()->load($backup);

                    return $executor;
                }
            }

            // TODO: handle case when using persistent connections. Fail loudly?
            $schemaTool = new SchemaTool($om);
            $schemaTool->dropDatabase();

            if (!empty($metadatas)) {
                $schemaTool->createSchema($metadatas);
            }

            $executor = new ORMExecutor($om, new ORMPurger());
            $executor->setReferenceRepository($referenceRepository);
        } else {
            throw new \Exception(sprintf('%s not supported !', get_class($connection->getDriver())));
        }

        $loader = $this->getFixtureLoader($container, $classNames);

        $executor->execute($loader->getFixtures(), $append);

        if (isset($name) && isset($backup)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $executor->getReferenceRepository()->save($backup);

            copy($name, $backup);
        }

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
     * @param ContainerInterface $container
     *
     * @return self
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }
}
