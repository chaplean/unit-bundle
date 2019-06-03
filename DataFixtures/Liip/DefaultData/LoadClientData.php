<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadClientData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.0.0
 */
class LoadClientData extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     *
     * @return void
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $client = new Client();

        $client->setName('Chaplean');
        $client->setCode('001');
        $client->setEmail('DEFAULT DATA !!!!');
        $client->setIsActive(true);
        $client->setIsPrivateMember(true);
        $client->setHasCode(false);
        $client->setDateAdd(new \DateTime('2018-01-01'));

        $manager->persist($client);
        $this->setReference('client-1', $client);

        $manager->flush();
    }
}
