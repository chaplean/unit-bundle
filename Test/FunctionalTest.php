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
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     1.0.0
 */

class FunctionalTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    /**
     * @var array
     */
    static $cachedMetadatas = array();

    /**
     * @var Container
     */
    static $container;

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
     * @param string  $omName     The name of object manager to use
     * @param string  $registryName The service id of manager registry to use
     * @param integer $purgeMode Sets the ORM purge mode
     *
     * @return ORMExecutor
     */
    public function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        return FixtureUtility::loadFixtures($classNames, 'functional', $omName, $registryName, $purgeMode);
    }

    /**
     * Load static data fixture
     *
     * @param array   $classNames List of fully qualified class names of fixtures to load
     * @param string  $omName     The name of object manager to use
     * @param string  $registryName The service id of manager registry to use
     * @param integer $purgeMode Sets the ORM purge mode
     *
     * @return ORMExecutor
     */
    public function loadStaticFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        return FixtureUtility::loadFixtures($classNames, 'functional', $omName, $registryName, $purgeMode);
    }
}
