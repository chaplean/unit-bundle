<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Functional;

use Chaplean\Bundle\UnitBundle\Entity\Client;

/**
 * TransactionTest.php.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.0.0
 */
class TransactionTest extends FunctionalTestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::setUpBeforeClass
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::setUp
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::enableTransactions
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::__get
     *
     * @return void
     * @throws \Exception
     */
    public function testBeforeAnnotationEmptyFixtureLoaded()
    {
        self::bootKernel();

        $this->assertCount(1, $this->em->getRepository(Client::class)->findAll());
        $this->assertTrue($this->em->isOpen());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::setUpBeforeClass
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::setUp
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::enableTransactions
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::__get
     *
     * @return void
     * @throws \Exception
     */
    public function testDuringTransaction()
    {
        self::bootKernel();

        $client = new Client();
        $client->setCode('test');
        $client->setName('pony');
        $client->setEmail('e@e.fr');
        $client->setDateAdd(new \DateTime());
        $client->setIsActive(false);
        $client->setIsPrivateMember(false);
        $client->setHasCode(false);
        
        $this->em->persist($client);
        $this->em->flush();

        $this->assertCount(2, $this->em->getRepository(Client::class)->findAll());
        $this->assertTrue($this->em->getConnection()->isTransactionActive());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::tearDown
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::rollbackTransactions
     * @covers \Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase::__get
     *
     * @return void
     * @throws \Exception
     */
    public function testAfterAnnotationWithoutFixture()
    {
        self::bootKernel();

        $this->assertCount(1, $this->em->getRepository(Client::class)->findAll());
    }
}
