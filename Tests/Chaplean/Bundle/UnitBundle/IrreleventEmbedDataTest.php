<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Entity\Invoice;
use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * IrreleventEmbedDataTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     3.0.0
 */
class IrreleventEmbedDataTest extends LogicalTest
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::loadStaticFixtures(array(
            'Chaplean\Bundle\UnitBundle\DataFixtures\Liip\LoadInvoiceData'
        ));
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
