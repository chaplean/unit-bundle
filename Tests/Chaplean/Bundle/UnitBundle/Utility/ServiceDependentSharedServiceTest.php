<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\SharedService;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * Class ServiceDependentSharedServiceTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     5.0.0
 */
class ServiceDependentSharedServiceTest extends WebTestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\ServiceDependentSharedService::__construct()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\ServiceDependentSharedService::callMockMe()
     *
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
     * @covers \Chaplean\Bundle\UnitBundle\Utility\ServiceDependentSharedService::__construct()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\ServiceDependentSharedService::callMockMe()
     *
     * @return void
     */
    public function testMockMockMeASecondTime()
    {
        $serviceSharedMock = \Mockery::mock(SharedService::class)->shouldReceive('mockMe')->andReturn('I am mock :D (again)')->getMock();
        $this->getContainer()->set('chaplean_unit.shared_service', $serviceSharedMock);

        $this->assertEquals('I am mock :D (again)', $this->getContainer()->get('chaplean_unit.service_dependent_on_shared_service')->callMockMe());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\ServiceDependentSharedService::__construct()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\ServiceDependentSharedService::dontCallMockMe()
     *
     * @return void
     * @expectedException \Mockery\Exception\InvalidCountException
     * @throws
     */
    public function testMockMockMeWithoutCallHim()
    {
        $serviceSharedMock = \Mockery::mock(SharedService::class)->shouldReceive('mockMe')->once()->andReturn('I am mock :D (again)')->getMock();
        $this->getContainer()->set('chaplean_unit.shared_service', $serviceSharedMock);

        $this->assertEquals('eenie meenie miney mo', $this->getContainer()->get('chaplean_unit.service_dependent_on_shared_service')->dontCallMockMe());

        \Mockery::close();
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\ServiceDependentSharedService::__construct()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\ServiceDependentSharedService::dontCallMockMe()
     *
     * @return void
     */
    public function testNotCallMockMe()
    {
        $serviceSharedMock = \Mockery::mock(SharedService::class)->shouldNotReceive('mockMe')->getMock();
        $this->getContainer()->set('chaplean_unit.shared_service', $serviceSharedMock);

        $this->assertEquals('eenie meenie miney mo', $this->getContainer()->get('chaplean_unit.service_dependent_on_shared_service')->dontCallMockMe());

        \Mockery::close();
    }
}
