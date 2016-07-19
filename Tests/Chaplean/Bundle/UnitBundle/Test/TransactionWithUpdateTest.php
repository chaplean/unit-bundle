<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;

/**
 * Class TransactionWithUpdateTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Test
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     4.0.0
 */
class TransactionWithUpdateTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public function testUpdateClient()
    {
        /** @var Client $client */
        $client = $this->getReference('client-1');

        $this->assertEquals('Chaplean', $client->getName());

        $client->setName('Update !');
        $this->em->persist($client);
        $this->em->flush();

        $this->assertEquals('Update !', $this->em->find('ChapleanUnitBundle:Client', $client->getId())->getName());
    }

    /**
     * @return void
     */
    public function testGetClientPreviouslyUpdated()
    {
        /** @var Client $clientReference */
        $clientReference = $this->getReference('client-1');
        $clientDatabase  = $this->em->getConnection()->executeQuery('SELECT name FROM cl_client WHERE id = 1')->fetch();

        $this->assertEquals('Chaplean', $clientDatabase['name']);
        $this->assertEquals('Chaplean', $clientReference->getName());
    }
}
