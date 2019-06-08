<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Functional;

/**
 * Class ClientTest.
 *
 * @package   App\Tests\Chaplean\Bundle\UnitBundle\Test
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2019 Chaplean (https://www.chaplean.coop)
 */
class ClientTest extends FunctionalTestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::createClient
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::enableTransactions
     *
     * @return void
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testClientHasTransaction(): void
    {
        $client = self::createClient();

        $this->assertTrue($client->getContainer()->get('doctrine')->getManager()->getConnection()->isTransactionActive());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::__get
     *
     * @return void
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testOverrideEmProperty(): void
    {
        $client = self::createClient();

        $this->assertSame($client->getContainer()->get('doctrine')->getManager(), $this->em);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::tearDown
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::rollbackTransactions
     *
     * @return void
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testRollbackTransactionOnTearDownAndEnsureShutdownClient(): void
    {
        $reflectionParent = new \ReflectionClass(FunctionalTestCase::class);
        $staticClient = $reflectionParent->getProperty('client');
        $staticClient->setAccessible(true);

        $client = self::createClient();

        $this->assertNotNull($staticClient->getValue(FunctionalTestCase::class));

        $this->tearDown();

        $this->assertNull($client->getContainer());
        $this->assertNull($staticClient->getValue(FunctionalTestCase::class));
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::getContainer
     *
     * @return void
     * @throws \Exception
     */
    public function testOverrideSelfGetContainer(): void
    {
        $client = self::createClient();

        $this->assertSame($this->getContainer(), $client->getContainer());
        $this->assertNotSame($this->getContainer(), self::$container);
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::createClient
     *
     * @return void
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    public function testGetReferenceBeforeCreateClient(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You must create client before the first getReference in your test');

        $this->getReference('provider-1');

        self::createClient();
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::createClient
     *
     * @return void
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testDoubleCreateClient(): void
    {
        $client = self::createClient();

        $this->assertSame($client, self::createClient());
    }
}