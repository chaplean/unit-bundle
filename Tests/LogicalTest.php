<?php
/**
 * LogicalTest.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     0.1.0
 */

namespace Chaplean\Bundle\UnitBundle\Tests;

use Chaplean\Bundle\UnitBundle\Utility\FixtureUtility;
use Doctrine\ORM\EntityManager;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class LogicalTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @param array  $classNames List of fully qualified class names of fixtures to load
     * @param string $omName     The name of object manager to use
     *
     * @return void
     */
    public static function loadStaticFixtures(array $classNames, $omName = null)
    {
        FixtureUtility::loadFixtures($classNames, $omName);
    }
}
