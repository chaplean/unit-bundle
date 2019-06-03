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
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2017 Chaplean (https://www.chaplean.coop)
 * @since     7.0.0
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
     * @return void
     * @throws \Exception
     *
     * @covers \Chaplean\Bundle\UnitBundle\Utility\FixtureLiteUtility
     */
    public function testLoadClass()
    {
        $referenceRepository = \Mockery::mock(ReferenceRepository::class);
        $referenceRepository->shouldReceive('save')
            ->once();

        $schemaTool = \Mockery::mock('overload:Doctrine\ORM\Tools\SchemaTool');
        $schemaTool->shouldReceive('dropDatabase')
            ->once();

        $ormexecutor = \Mockery::mock('overload:Doctrine\Common\DataFixtures\Executor\ORMExecutor');
        $ormexecutor->shouldReceive('setReferenceRepository')
            ->once();
        $ormexecutor->shouldReceive('execute')
            ->once();
        $ormexecutor->shouldReceive('getReferenceRepository')
            ->once()
            ->andReturn($referenceRepository);

        $sqliteDriver = \Mockery::mock(SqliteDriver::class);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('getDriver')
            ->once()
            ->andReturn($sqliteDriver);
        $connection->shouldReceive('getParams')
            ->once()
            ->andReturn(['path' => 'custompath']);

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
        $manager->shouldReceive('getConnection')
            ->once()
            ->andReturn($connection);

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
        $container->shouldReceive('getParameter')
            ->once()
            ->with('kernel.cache_dir')
            ->andReturn('');

        $fixture = FixtureLiteUtility::getInstance($container);

        $fixture->loadFixtures([]);
    }
}
