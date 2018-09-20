<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData\LoadClientData;
use Chaplean\Bundle\UnitBundle\Entity\User;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class AbstractFixtureTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Hugo - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2018 Chaplean (http://www.chaplean.coop)
 */
class AbstractFixtureTest extends MockeryTestCase
{
    /**
     * @var AbstractFixture|\Mockery\Mock
     */
    protected $utility;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->utility = \Mockery::mock(AbstractFixture::class)->makePartial();
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\AbstractFixture::initGenerator()
     *
     * @return void
     */
    public function testInitGenerator()
    {
        $entity = new User();

        $this->utility->initGenerator($entity);

        $this->assertTrue(true);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\AbstractFixture::initGenerator()
     *
     * @expectedException \Exception
     *
     * @return void
     */
    public function testInitGeneratorNotExistingFile()
    {
        $entity = new LoadClientData();

        $this->utility->initGenerator($entity);
    }
}
