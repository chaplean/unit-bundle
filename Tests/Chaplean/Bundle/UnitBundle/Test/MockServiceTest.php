<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility;
use FOS\RestBundle\Tests\Functional\WebTestCase;

/**
 * Class MockServiceTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Test
 * @author    Tom - Chaplean <tom@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     4.2.0
 */
class MockServiceTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testGetSwiftMailerCacheMockNotFunctional()
    {
        $logicalTestCase = new LogicalTestCase();
        $logicalTestCase::setUpBeforeClass();
        $logicalTestCase->setUp();

        $swiftMock = \Mockery::mock(SwiftMailerCacheUtility::class);

        $serviceInstance = $logicalTestCase->getContainer()
            ->get('chaplean_unit.service_instance');

        $this->assertEquals('Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility', $serviceInstance->getSwiftMailerCacheClass());

        // We mock it but because SwiftMailer has been called before it's useless
        $logicalTestCase->getContainer()
            ->set('chaplean_unit.swiftmailer_cache', $swiftMock);

        $serviceInstance2 = $logicalTestCase->getContainer()
            ->get('chaplean_unit.service_instance');

        $this->assertEquals('Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility', $serviceInstance2->getSwiftMailerCacheClass());

        $logicalTestCase->getContainer()
            ->set('chaplean_unit.swiftmailer_cache', null);

        $logicalTestCase->tearDown();
        $logicalTestCase::tearDownAfterClass();
    }

    /**
     * @return void
     */
    public function testMockServiceDestroyedOnTeardown()
    {
        $logicalTestCase = new LogicalTestCase();
        $logicalTestCase::setUpBeforeClass();
        $logicalTestCase->setUp();

        $swiftMock = \Mockery::mock(SwiftMailerCacheUtility::class);

        // We mock it but because SwiftMailer has been called before it's useless
        $logicalTestCase->mockService('chaplean_unit.swiftmailer_cache', $swiftMock);

        $swiftMailer = $logicalTestCase->getContainer()
            ->get('chaplean_unit.swiftmailer_cache');

        $this->assertNotEquals('Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility', get_class($swiftMailer));

        $logicalTestCase->tearDown();
        $logicalTestCase::tearDownAfterClass();

        $swiftMailer = $logicalTestCase->getContainer()
            ->get('chaplean_unit.swiftmailer_cache');

        $this->assertEquals('Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility', get_class($swiftMailer));
    }

    /**
     * @return void
     */
    public function testGetServiceRefreshed()
    {
        $logicalTestCase = new LogicalTestCase();
        $logicalTestCase::setUpBeforeClass();
        $logicalTestCase->setUp();

        $swiftMock = \Mockery::mock(SwiftMailerCacheUtility::class);

        // MockService alone is useless
        $logicalTestCase->mockService('chaplean_unit.swiftmailer_cache', $swiftMock);

        $serviceInstance = $logicalTestCase->getContainer()
            ->get('chaplean_unit.service_instance');

        $this->assertEquals('Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility', $serviceInstance->getSwiftMailerCacheClass());

        $serviceInstance2 = $logicalTestCase->getServiceRefreshed('chaplean_unit.service_instance');

        $this->assertNotEquals('Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility', $serviceInstance2->getSwiftMailerCacheClass());
        $this->assertEquals(
            $logicalTestCase->getContainer()
                ->get('chaplean_unit.service_instance'),
            $serviceInstance2
        );

        $logicalTestCase->tearDown();
        $logicalTestCase::tearDownAfterClass();

        $serviceInstance3 = $logicalTestCase->getContainer()
            ->get('chaplean_unit.service_instance');

        $this->assertEquals('Chaplean\Bundle\UnitBundle\Utility\SwiftMailerCacheUtility', $serviceInstance3->getSwiftMailerCacheClass());
    }
}
