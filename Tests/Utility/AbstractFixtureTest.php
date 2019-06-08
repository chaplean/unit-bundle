<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\Chaplean\Bundle\UnitBundle\Functional\FunctionalTestCase;

/**
 * Class AbstractFixtureTest.
 *
 * @package             Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author              Hugo - Chaplean <tom@chaplean.coop>
 * @copyright           2014 - 2018 Chaplean (https://www.chaplean.coop)
 */
class AbstractFixtureTest extends FunctionalTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var AbstractFixture
     */
    private $fixtureClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureClass = new class() extends AbstractFixture
        {
            public function load(ObjectManager $manager)
            {
            }
        };
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetReferenceWithId(): void
    {
        $mockReferenceRepository = \Mockery::mock(ReferenceRepository::class);
        $mockReferenceRepository->shouldReceive('getReference')
            ->once()
            ->andReturn(new class() { public function getId() { return 1; }});

        $this->fixtureClass->setReferenceRepository($mockReferenceRepository);
        $this->fixtureClass->getReference('good-ref');
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetReferenceWithoutId(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('\'bad-ref\' is not persisted !');

        $mockReferenceRepository = \Mockery::mock(ReferenceRepository::class);
        $mockReferenceRepository->shouldReceive('getReference')
            ->once()
            ->andReturn(new class() { public function getId() { return null; }});

        $this->fixtureClass->setReferenceRepository($mockReferenceRepository);
        $this->fixtureClass->getReference('bad-ref');
    }
}
