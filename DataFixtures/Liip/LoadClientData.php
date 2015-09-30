<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip;

use Chaplean\Bundle\UnitBundle\Entity\Client;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
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

        $manager->persist($client);
        $this->setReference('client-1', $client);

        $manager->flush();
    }
}
