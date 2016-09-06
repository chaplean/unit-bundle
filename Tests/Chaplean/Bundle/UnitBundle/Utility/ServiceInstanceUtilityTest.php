<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\ServiceInstanceUtility;

/**
 * Class ServiceInstanceUtilityTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Tom - Chaplean <tom@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     4.2.0
 */
class ServiceInstanceUtilityTest extends LogicalTestCase
{
    /**
     * @var ServiceInstanceUtility
     */
    private $serviceInstance;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->serviceInstance = $this->getContainer()
            ->get('chaplean_unit.service_instance');
    }

    /**
     * @return void
     */
    public function testService()
    {
        $this->assertInstanceOf(ServiceInstanceUtility::class, $this->serviceInstance);
    }

    /**
     * @return void
     */
    public function testGetSwiftMailerCacheClassReturnsCacheClass()
    {
        $this->assertEquals('Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility', $this->serviceInstance->getSwiftMailerCacheClass());
    }
}
