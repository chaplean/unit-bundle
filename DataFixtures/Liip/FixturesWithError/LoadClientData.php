<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\FixturesWithError;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadClientData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.0
 */
class LoadClientData extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $client = new Client();
        $this->setReference('client-1', $client);
        $this->persist($client, $manager);

        $client1 = new Client();
        $client1->setName('Chaplean');
        $client1->setCode('001');
        $this->setReference('client-2', $client1);
        $this->persist($client, $manager);

        $manager->flush();
    }
}
