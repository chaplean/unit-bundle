<?php

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;

/**
 * FunctionalTest.
 *
 * @author     Valentin - Chaplean <valentin@chaplean.com>
 * @copyright  2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since      1.0.0
 * @deprecated See Behat !!
 */
class FunctionalTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * @var array
     */
    protected static $cachedMetadatas = array();

    /**
     * @var Container
     */
    protected static $container;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * SetUp default for test selenium
     *
     * @return void
     */
    protected function setUp()
    {
        $this->baseUrl = self::$container->getParameter('base_url_selenium');
        $this->em = self::$container->get('doctrine')->getManager();

        $this->setBrowser('firefox');
        $this->setBrowserUrl($this->baseUrl);
    }

    /**
     * Load data fixture
     *
     * @param array   $classNames List of fully qualified class names of fixtures to load
     * @param integer $purgeMode  Sets the ORM purge mode
     *
     * @return ORMExecutor
     */
    public function loadFixtures(array $classNames, $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        return FixtureUtility::loadFixtures($classNames, 'functional', $purgeMode);
    }

    /**
     * Load static data fixture
     *
     * @param array   $classNames List of fully qualified class names of fixtures to load
     * @param integer $purgeMode  Sets the ORM purge mode
     *
     * @return ORMExecutor
     */
    public function loadStaticFixtures(array $classNames, $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        return FixtureUtility::loadFixtures($classNames, 'functional', $purgeMode);
    }

    /**
     * Get the container
     *
     * @return Container
     */
    public static function getStaticContainer()
    {
        return FixtureUtility::getContainer('functional');
    }

    /**
     * Close connection to avoid "Too Many Connection" error
     *
     * @return void
     */
    public function tearDown()
    {
        $this->em->getConnection()->close();

        parent::tearDown();
    }
}
