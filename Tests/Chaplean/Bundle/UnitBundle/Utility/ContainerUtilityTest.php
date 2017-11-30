<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\ContainerUtility;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * ContainerUtilityTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class ContainerUtilityTest extends WebTestCase
{
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
