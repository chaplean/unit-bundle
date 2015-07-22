<?php
/**
 * LogicalTest.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     0.1.0
 */

namespace Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;

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
     * Construct
     */
    public function __construct()
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @param array   $classNames List of fully qualified class names of fixtures to load
     * @param string  $omName     The name of object manager to use
     * @param string  $registryName The service id of manager registry to use
     * @param integer $purgeMode Sets the ORM purge mode
     *
     * @return void
     */
    public static function loadStaticFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        FixtureUtility::loadFixtures($classNames, 'logical', $omName, $registryName, $purgeMode);
    }

    public function loadFixtures(array $classNames, $omName = null, $registryName = 'doctrine', $purgeMode = ORMPurger::PURGE_MODE_TRUNCATE)
    {
        FixtureUtility::loadFixtures($classNames, 'logical', $omName, $registryName, $purgeMode);
    }
}
