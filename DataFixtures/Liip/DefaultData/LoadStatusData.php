<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData;

use Chaplean\Bundle\UnitBundle\Entity\Status;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadStatusData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.0.0
 */
class LoadStatusData extends AbstractFixture
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $datas = [
            'active'   => ['active'],
            'inactive' => ['inactive'],
            'deleted'  => ['deleted'],
        ];

        foreach ($datas as $key => $data) {
            $status = new Status();
            $status->setStatus($data[0]);

            $this->persist($status, $manager);
            $this->setReference('status-' . $key, $status);
        }

        $manager->flush();
    }
}
