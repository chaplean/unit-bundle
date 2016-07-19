<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;

/**
 * Class RestClientTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Tom - Chaplean <tom@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     4.0.0
 */
class RestClientTest extends LogicalTestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::loadStaticFixtures();
        parent::setUpBeforeClass();
    }

    /**
     * @expectedException \Exception
     *
     * @return void
     */
    public function testGetResponseNotFlush()
    {
        $client = new RestClient($this->getContainer());

        $client->getContent();
    }

    /**
     * @return void
     */
    public function testGetResponse()
    {
        $client = new RestClient($this->getContainer());

        $response = $client->request('GET', '/');

        $this->assertEquals($response, $client->getResponse());
    }

    /**
     * @return void
     */
    public function testGetContainer()
    {
        $client = new RestClient($this->getContainer());

        $this->assertEquals($this->getContainer(), $client->getContainer());
    }
}
