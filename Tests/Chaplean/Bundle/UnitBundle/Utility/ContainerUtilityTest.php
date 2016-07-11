<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\ContainerUtility;

/**
 * ContainerUtilityTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class ContainerUtilityTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::loadStaticFixtures();
        parent::setUpBeforeClass();
    }

    /**
     * @return void
     */
    public function testGetContainerLogicalTest()
    {
        $container = ContainerUtility::getContainer('logical');

        $this->assertInstanceOf('\AppKernel', $container->get('kernel'));
        $this->assertEquals('test', $container->get('kernel')->getEnvironment());
    }

    /**
     * @return void
     */
    public function testGetContainerWithoutType()
    {
        ContainerUtility::loadContainer('');
        $container = ContainerUtility::getContainer('');

        $this->assertInstanceOf('\AppKernel', $container->get('kernel'));
        $this->assertEquals('test', $container->get('kernel')->getEnvironment());
    }

    /**
     * @return void
     */
    public function testLoadContainer()
    {
        ContainerUtility::loadContainer('logical');

        $this->assertInstanceOf('\AppKernel', ContainerUtility::getContainer('logical')->get('kernel'));
    }
}
