<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\ContainerUtility;
use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Chaplean\Bundle\UnitBundle\Utility\NamespaceUtility;
use Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Entity;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * LogicalTest.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     0.1.0
 */
class LogicalTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var SwiftMailerCacheUtility
     */
    protected $swiftmailerCacheUtility;

    /**
     * @var ReferenceRepository
     */
    protected static $fixtures;

    /**
     * @var array
     */
    protected static $defaultFixtures;

    /**
     * @var boolean
     */
    protected static $iWantDefaultData = true;

    /**
     * @var boolean
     */
    protected static $databaseLoaded = false;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->swiftmailerCacheUtility = $this->getContainer()->get('chaplean_unit.swiftmailer_cache');

        $file = new \ReflectionClass(get_called_class());
        $name = $file->name;
        FixtureUtility::$namespace = substr($name, 0, strpos($name, 'Tests'));
        self::$databaseLoaded = false;
    }

    /**
     * @return void
     */
    public function cleanMailDir()
    {
        $this->swiftmailerCacheUtility->cleanMailDir();
    }

    /**
     * @param string $reference
     *
     * @return Entity|null
     */
    public function getRealEntity($reference)
    {
        $entity = self::$fixtures->getReference($reference);

        return $this->em->find(get_class($entity), $entity->getId());
    }

    /**
     * Get the container
     *
     * @return Container
     */
    public static function getStaticContainer()
    {
        return ContainerUtility::getContainer('logical');
    }

    /**
     * @param string|null $namespace
     *
     * @return void
     */
    public static function loadDefaultFixtures($namespace = null)
    {
        self::$defaultFixtures = FixtureUtility::loadDefaultFixtures($namespace);
    }

    /**
     * @param array    $classNames
     * @param string   $omName
     * @param string   $registryName
     * @param int|null $purgeMode
     *
     * @return void
     * @deprecated Use loadStaticFixtures ! Transaction is active by function test
     */
    public function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        unset($omName);
        unset($registryName);

        self::$fixtures = FixtureUtility::loadFixtures($classNames, 'logical', $purgeMode)->getReferenceRepository();
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public static function loadFixturesByContext($context)
    {
        $defaultFixtures = NamespaceUtility::getClassNamesByContext(FixtureUtility::$namespace, $context);

        if (!empty($defaultFixtures)) {
            if (empty(self::$fixtures)) {
                self::loadStaticFixtures($defaultFixtures);
            } else {
                self::$fixtures = FixtureUtility::loadPartialFixtures($defaultFixtures, null)->getReferenceRepository();
            }
        }
    }

    /**
     * @param array $classNames List of fully qualified class names of fixtures to load
     *
     * @return void
     */
    public function loadPartialFixtures(array $classNames)
    {
        self::$fixtures = FixtureUtility::loadPartialFixtures($classNames, $this->em)->getReferenceRepository();
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public function loadPartialFixturesByContext($context)
    {
        $classNames = NamespaceUtility::getClassNamesByContext(FixtureUtility::$namespace, $context);
        self::$fixtures = FixtureUtility::loadPartialFixtures($classNames, $this->em)->getReferenceRepository();
    }

    /**
     * @param array $classNames
     * @param int   $purgeMode
     *
     * @return void
     */
    public static function loadStaticFixtures(array $classNames, $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        self::$fixtures = FixtureUtility::loadFixtures($classNames, 'logical', $purgeMode)->getReferenceRepository();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function readMessages()
    {
        return $this->swiftmailerCacheUtility->readMessages();
    }

    /**
     * Load empty data fixture to generate the database schema even if no data are given
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        $args = func_get_args();
        if (!empty($args)) {
            $datafixtures = $args[0];
        }

        parent::setUpBeforeClass();
        self::$defaultFixtures = array();

        if (self::$iWantDefaultData) {
            self::loadDefaultFixtures();
        } else {
            self::$iWantDefaultData = true;
        }

        if (!empty($datafixtures)) {
            self::$defaultFixtures = array_merge(self::$defaultFixtures, $datafixtures);
        }

        self::loadStaticFixtures(self::$defaultFixtures);
        self::$databaseLoaded = true;
    }

    /**
     * Start transaction
     *
     * @return void
     */
    public function setUp()
    {
        if (self::$databaseLoaded) {
            $this->em->beginTransaction();
        }
        $this->cleanMailDir();

        parent::setUp();
    }

    /**
     * Close connection to avoid "Too Many Connection" error and rollback transaction
     *
     * @return void
     */
    public function tearDown()
    {
        if (!$this->em->getConnection()->isAutoCommit() && self::$databaseLoaded) {
            $this->em->rollback();
        }

        $this->em->getConnection()->close();

        parent::tearDown();
    }
}
