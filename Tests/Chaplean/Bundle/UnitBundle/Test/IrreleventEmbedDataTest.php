<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Test;

use Chaplean\Bundle\UnitBundle\Entity\Invoice;
use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;

/**
 * IrreleventEmbedDataTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     3.0.0
 */
class IrreleventEmbedDataTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::loadStaticFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadInvoiceData'
        ));
        parent::setUpBeforeClass();
    }

    /**
     * @return void
     */
    public function testIrreleventInvoice()
    {
        /** @var Invoice $invoice */
        $invoice = self::$fixtures->getReference('invoice-1');

        $this->assertEquals('Chaplean', $invoice->getClient()->getName());
        $this->assertEquals('0', $invoice->getEmbed()->getCode());
    }
}
