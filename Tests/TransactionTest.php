<?php

namespace Chaplean\Bundle\UnitBundle\Tests;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * TransactionTest.php.
 *
 * @author    Matthias - Chaplean <matthias@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class TransactionTest extends LogicalTest
{
    /**
     * @return void
     */
    public function testBeforeAnnotationEmptyFixtureLoaded()
    {
        $this->assertCount(0, $this->em->getRepository('ChapleanUnitBundle:Client')->findAll());
    }

    /**
     * @return void
     */
    public function testDuringTransaction()
    {
        $client = new Client();
        $client->setCode('test');
        $client->setName('pony');
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
}