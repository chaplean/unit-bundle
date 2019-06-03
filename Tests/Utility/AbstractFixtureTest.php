<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class AbstractFixtureTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Hugo - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2018 Chaplean (https://www.chaplean.coop)
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class AbstractFixtureTest extends MockeryTestCase
{
    /**
     * @return void
     * @throws \Exception
     */
    public function testGetReferenceWithId()
    {
        $parentClass = \Mockery::mock('overload:Doctrine\Common\DataFixtures\AbstractFixture');
        $parentClass->shouldReceive('getReference')
            ->once()
            ->andReturn(new class() { public function getId() { return 1; }});

        $fixture = new class() extends AbstractFixture {
            public function load(ObjectManager $manager)
            {}
        };
        $fixture->getReference('my-reference');
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetReferenceWithoutId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('\'my-reference\' is not persisted !');

        $parentClass = \Mockery::mock('overload:Doctrine\Common\DataFixtures\AbstractFixture');
        $parentClass->shouldReceive('getReference')
            ->once()
            ->andReturn(new class() { public function getId() { return null; }});

        $fixture = new class() extends AbstractFixture {
            public function load(ObjectManager $manager)
            {}
        };
        $fixture->getReference('my-reference');
    }
}
