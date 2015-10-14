<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
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
     * @var ReferenceRepository
     */
    protected static $fixtures;

    /**
     * Construct
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @param array          $classNames List of fully qualified class names of fixtures to load
     * @param integer|string $purgeMode  Sets the ORM purge mode
     *
     * @return void
     */
    public function loadFixtures(array $classNames, $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        self::$fixtures = FixtureUtility::loadFixtures($classNames, 'logical', $purgeMode)->getReferenceRepository();
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
     * Get the container
     *
     * @return Container
     */
    public static function getStaticContainer()
    {
        return FixtureUtility::getContainer('logical');
    }

    /**
     * Load empty data fixture to generate the database schema even if no data are given
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::loadFixtures(array());

        parent::setUpBeforeClass();
    }

    /**
     * Start transaction
     *
     * @return void
     */
    public function setUp()
    {
        $this->em->beginTransaction();

        parent::setUp();
    }

    /**
     * @param string $reference
     *
     * @return Entity
     */
    public function getRealEntity($reference)
    {
        $entity = self::$fixtures->getReference($reference);

        return $this->em->find(get_class($entity), $entity->getId());
    }

    /**
     * Close connection to avoid "Too Many Connection" error and rollback transaction
     *
     * @return void
     */
    public function tearDown()
    {
        $this->em->rollback();

        $this->em->getConnection()->close();

        parent::tearDown();
    }
}
