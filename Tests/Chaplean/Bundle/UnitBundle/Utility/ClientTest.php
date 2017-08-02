<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;

/**
 * Class RestClientTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Tom - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
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

    /**
     * @return void
     */
    public function testGetCurrentRequest()
    {
        $client = $this->createRestClient();
        $client->request('GET', '/', array(), array(), array(), array(), array(), array(), array('REMOTE_ADDR' => '0.0.0.0'));

        $this->assertEquals('0.0.0.0', $client->getRequest()->server->get('REMOTE_ADDR'));
        $this->assertEquals('0.0.0.0', $client->getResponse()->getContent());
        $this->assertEquals('0.0.0.0', $this->getContainer()->get('request_stack')->getCurrentRequest()->getClientIp());
    }

    /**
     * @return void
     */
    public function testGetAnotherRemoteAddress()
    {
        $client = $this->createRestClient();
        $client->request('GET', '/', array(), array(), array(), array(), array(), array(), array('REMOTE_ADDR' => '1.1.1.1'));

        $this->assertEquals('1.1.1.1', $client->getRequest()->server->get('REMOTE_ADDR'));
        $this->assertEquals('1.1.1.1', $client->getResponse()->getContent());
        $this->assertEquals('1.1.1.1', $this->getContainer()->get('request_stack')->getCurrentRequest()->getClientIp());
    }
}
