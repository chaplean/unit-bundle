<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
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
    protected $fixtures;

    /**
     * @var AnnotationReader $annotationReader
     */
    protected $annotationReader;

    /**
     * @var string $annotationClass
     */
    private $annotationClass = 'Chaplean\\Bundle\\UnitBundle\\Annotations\\Transaction';

    /**
     * @var \ReflectionObject
     */
    private $reflectionObject;

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

        $this->em = $this->getContainer()
                         ->get('doctrine')
                         ->getManager();

        $this->annotationReader = new AnnotationReader();
        $this->reflectionObject = new \ReflectionObject($this);
    }

    /**
     * @param array   $classNames   List of fully qualified class names of fixtures to load
     * @param string  $omName       The name of object manager to use
     * @param string  $registryName The service id of manager registry to use
     * @param integer $purgeMode    Sets the ORM purge mode
     *
     * @return ORMExecutor
     */
    public static function loadStaticFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        return FixtureUtility::loadFixtures($classNames, 'logical', $omName, $registryName, $purgeMode);
    }

    /**
     * @param array   $classNames   List of fully qualified class names of fixtures to load
     * @param string  $omName       The name of object manager to use
     * @param string  $registryName The service id of manager registry to use
     * @param integer $purgeMode    Sets the ORM purge mode
     *
     * @return ORMExecutor
     */
    public function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        return FixtureUtility::loadFixtures($classNames, 'logical', $omName, $registryName, $purgeMode);
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
        self::loadStaticFixtures(array());

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
     * Close connection to avoid "Too Many Connection" error and rollback transaction
     *
     * @return void
     */
    public function tearDown()
    {
        $this->em->rollback();

        $this->em->getConnection()
                 ->close();
        
        parent::tearDown();
    }
}
