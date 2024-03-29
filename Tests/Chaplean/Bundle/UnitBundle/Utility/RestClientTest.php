<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Test\FunctionalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;

/**
 * RestClientTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (https://www.chaplean.coop)
 * @since     2.1.0
 */
class RestClientTest extends FunctionalTestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\RestClient::getContent()
     *
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Not response flush !
     */
    public function testGetContentClient()
    {
        $client = new RestClient($this->getContainer());

        $client->getContent();
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\RestClient::getContent()
     *
     * @return void
     */
    public function testInstantiateRestClient()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('GET', '/rest/unit/object');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            [
                'id'   => 1,
                'name' => 'foo',
            ],
            $restClient->getContent()
        );
    }
}
