<?php

namespace Chaplean\Bundle\UnitBundle\DataFixtures\Liip;

use Chaplean\Bundle\UnitBundle\Entity\Invoice;
use Chaplean\Bundle\UnitBundle\Utility\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadInvoiceData.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     3.0.0
 */
class LoadInvoiceData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @param ObjectManager $manager
     *
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $invoice = new Invoice();

        $this->persist($invoice, $manager);
        $this->setReference('invoice-1', $invoice);

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\DefaultData\LoadClientData'
        );
    }
}
