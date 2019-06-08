<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FixtureLiteUtilityTest.
 *
 * @package             Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author              Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright           2014 - 2017 Chaplean (https://www.chaplean.coop)
 * @since               7.0.0
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FixtureLiteUtilityTest extends MockeryTestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility::getInstance()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility::setContainer()
     *
     * @return void
     */
    public function testGetInstance()
    {
        /** @var Container|MockInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);

        $instance1 = FixtureLiteUtility::getInstance($container);

        $this->assertInstanceOf(FixtureLiteUtility::class, $instance1);

        $instance2 = FixtureLiteUtility::getInstance($container);

        $this->assertEquals(spl_object_hash($instance1), spl_object_hash($instance2));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility
     *
     * @return void
     * @throws \Exception
     */
    public function testLoadClass()
    {
        $schemaTool = \Mockery::mock('overload:Doctrine\ORM\Tools\SchemaTool');
        $schemaTool->shouldReceive('dropDatabase')
            ->once();

        $ormexecutor = \Mockery::mock('overload:Doctrine\Common\DataFixtures\Executor\ORMExecutor');
        $ormexecutor->shouldReceive('setReferenceRepository')
            ->once();
        $ormexecutor->shouldReceive('execute')
            ->once();

        $classMetadataFactory = \Mockery::mock(ClassMetadataFactory::class);
        $classMetadataFactory->shouldReceive('getCacheDriver')
            ->once()
            ->andReturnNull();
        $classMetadataFactory->shouldReceive('getAllMetadata')
            ->once()
            ->andReturn([]);

        $manager = \Mockery::mock(EntityManagerInterface::class);
        $manager->shouldReceive('getMetadataFactory')
            ->twice()
            ->andReturn($classMetadataFactory);

        $om = \Mockery::mock(ObjectManager::class);
        $om->shouldReceive('getManager')
            ->once()
            ->andReturn($manager);

        /** @var Container|MockInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with('doctrine')
            ->andReturn($om);

        $fixture = FixtureLiteUtility::getInstance($container);

        $fixture->loadFixtures([]);
    }
}
