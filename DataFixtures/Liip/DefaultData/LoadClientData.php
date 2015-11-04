<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData;

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

        $client->setName('Chaplean');
        $client->setCode('001');
        $client->setEmail('DEFAULT DATA !!!!');

        $this->persist($client, $manager);
        $this->setReference('client-1', $client);

        $manager->flush();
    }
}
