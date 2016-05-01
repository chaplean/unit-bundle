<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;

/**
 * FrontClient.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     2.2.0
 */
class FrontClientTest extends LogicalTest
{
    /**
     * @return void
     */
    public function testGetIndexAction()
    {
        $client = $this->createFrontClient();
        $client->request('GET', '/');
    }
}