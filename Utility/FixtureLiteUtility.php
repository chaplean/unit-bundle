<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FixtureLiteUtility.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
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
     * Retrieve Doctrine DataFixtures loader.
     *
     * @param ContainerInterface $container
     * @param array              $classNames
     *
     * @return Loader
     */
    protected function getFixtureLoader(ContainerInterface $container, array $classNames): Loader
    {
        $loaderClass = 'Symfony\Bundle\DoctrineFixturesBundle\Common\DataFixtures\Loader';

        if (\class_exists('Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader')) {
            $loaderClass = 'Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader';
        } elseif (\class_exists('Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader')) {
            $loaderClass = 'Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader';
        }

        $loader = new $loaderClass($container);

        foreach ($classNames as $className) {
            self::loadFixtureClass($loader, $className);
        }

        return $loader;
    }

    /**
     * @param array $classNames
     *
     * @return string
     */
    public function getHash(array $classNames): string
    {
        return \md5(\serialize(self::$cachedMetadatas['default']) . \serialize($classNames) . \date('YMDH'));
    }

    /**
     * Get Singleton Instance
     *
     * @param ContainerInterface $container
     *
     * @return self
     */
    public static function getInstance(ContainerInterface $container): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
            self::$instance->setContainer($container);
        }

        return self::$instance;
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
    public function loadFixtures(array $classNames, bool $append = true): ORMExecutor
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

        if (!isset(self::$cachedMetadatas['default'])) {
            self::$cachedMetadatas['default'] = $om->getMetadataFactory()->getAllMetadata();

            \usort(
                self::$cachedMetadatas['default'],
                function ($a, $b) {
                    return \strcmp($a->name, $b->name);
                }
            );
        }

        $metadatas = self::$cachedMetadatas['default'];

        // TODO: handle case when using persistent connections. Fail loudly?
        $schemaTool = new SchemaTool($om);
        $schemaTool->dropDatabase();

        if (!empty($metadatas)) {
            $schemaTool->createSchema($metadatas);
        }

        $executor = new ORMExecutor($om, new ORMPurger());
        $executor->setReferenceRepository($referenceRepository);

        $loader = $this->getFixtureLoader($container, $classNames);

        $executor->execute($loader->getFixtures(), $append);

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
    private function loadFixtureClass(Loader $loader, string $className): void
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
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }
}
