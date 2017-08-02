<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;

/**
 * TransactionTest.php.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class TransactionTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::loadStaticFixtures();
        parent::setUpBeforeClass();
    }

    /**
     * @return void
     */
    public function testBeforeAnnotationEmptyFixtureLoaded()
    {
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertTrue($this->em->isOpen());
    }

    /**
     * @return void
     */
    public function testDuringTransaction()
    {
        $client = new Client();
        $client->setCode('test');
        $client->setName('pony');
        $client->setEmail('e@e.fr');
        $client->setDateAdd(new \DateTime());
        $client->setIsActive(false);
        $client->setIsPrivateMember(false);
        $client->setHasCode(false);
        
        $this->em->persist($client);
        $this->em->flush($client);

        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertTrue($this->em->getConnection()->isTransactionActive());
    }

    /**
     * @return void
     */
    public function testAfterAnnotationWithoutFixture()
    {
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
    }

    /**
     * @return void
     */
    public function testDuringTransactionWithFixture()
    {
        $this->loadPartialFixtures(array('Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadClientData'));

        $this->assertCount(1, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
        $this->assertTrue($this->em->getConnection()->isTransactionActive());
    }

    /**
     * @return void
     */
    public function testAfterAnnotationWithoutFixtureFile()
    {
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
    }
}
