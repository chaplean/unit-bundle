<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\SharedService;

/**
 * Class ServiceDependentSharedServiceTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     X.Y.Z
 */
class ServiceDependentSharedServiceTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public function testMockMockMe()
    {
        $serviceSharedMock = \Mockery::mock(SharedService::class);
        $this->getContainer()->set('chaplean_unit.shared_service', $serviceSharedMock);

        $serviceSharedMock->shouldReceive('mockMe')->andReturn('I am mock :D');

        $this->assertEquals('I am mock :D', $this->getContainer()->get('chaplean_unit.service_dependent_on_shared_service')->callMockMe());
    }

    /**
     * @return void
     */
    public function testMockMockMeASecondTime()
    {
        $serviceSharedMock = \Mockery::mock(SharedService::class)->shouldReceive('mockMe')->andReturn('I am mock :D (again)')->getMock();
        $this->getContainer()->set('chaplean_unit.shared_service', $serviceSharedMock);

        $this->assertEquals('I am mock :D (again)', $this->getContainer()->get('chaplean_unit.service_dependent_on_shared_service')->callMockMe());
    }

    /**
     * @return void
     * @expectedException Mockery\Exception\InvalidCountException
     * @throws
     */
    public function testMockMockMeWithoutCallHim()
    {
        $serviceSharedMock = \Mockery::mock(SharedService::class)->shouldReceive('mockMe')->once()->andReturn('I am mock :D (again)')->getMock();
        $this->getContainer()->set('chaplean_unit.shared_service', $serviceSharedMock);

        $this->assertEquals('eenie meenie miney mo', $this->getContainer()->get('chaplean_unit.service_dependent_on_shared_service')->dontCallMockMe());
        $this->throwMockerysException();
    }

    /**
     * @return void
     */
    public function testNotCallMockMe()
    {
        $serviceSharedMock = \Mockery::mock(SharedService::class)->shouldNotReceive('mockMe')->getMock();
        $this->getContainer()->set('chaplean_unit.shared_service', $serviceSharedMock);

        $this->assertEquals('eenie meenie miney mo', $this->getContainer()->get('chaplean_unit.service_dependent_on_shared_service')->dontCallMockMe());
        $this->throwMockerysException();
    }
}
