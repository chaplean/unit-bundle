<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Functional;

use Chaplean\Bundle\UnitBundle\Entity\Client;

/**
 * Class TransactionWithUpdateTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Test
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (https://www.chaplean.coop)
 * @since     4.0.0
 */
class TransactionWithUpdateTest extends FunctionalTestCase
{
    /**
     * @return void
     * @throws \Exception
     */
    public function testUpdateClient()
    {
        self::bootKernel();

        /** @var Client $client */
        $client = $this->getReference('client-1');

        $this->assertEquals('Chaplean', $client->getName());
        $client->setName('Update !');

        $this->em->persist($client);
        $this->em->flush();

        $this->assertEquals('Update !', $this->em->find(Client::class, $client->getId())->getName());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetClientPreviouslyUpdated()
    {
        self::bootKernel();

        $clientDatabase  = $this->em->getConnection()->executeQuery('SELECT name FROM cl_client WHERE id = 1')->fetch();

        $this->assertEquals('Chaplean', $clientDatabase['name']);

        /** @var Client $clientReference */
        $clientReference = $this->getReference('client-1');

        $this->assertEquals('Chaplean', $clientReference->getName());
    }
}
