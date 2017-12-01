<?php

namespace Tests\Chaplean\Bundle\UnitBundle\Utility;

use Chaplean\Bundle\UnitBundle\Utility\Client;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ClientTest.
 *
 * @package   Tests\Chaplean\Bundle\UnitBundle\Utility
 * @author    Tom - Chaplean <tom@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     4.0.0
 */
class ClientTest extends WebTestCase
{
    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getResponse()
     *
     * @return void
     */
    public function testGetResponse()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('GET', '/');

        $this->assertEquals($response, $client->getResponse());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getContainer()
     *
     * @return void
     */
    public function testGetContainer()
    {
        $client = new Client($this->getContainer());

        $this->assertEquals($this->getContainer(), $client->getContainer());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRequest()
     *
     * @return void
     */
    public function testGetCurrentRequest()
    {
        $client = new Client($this->getContainer());
        $client->request('GET', '/', [], [], [], [], [], [], ['REMOTE_ADDR' => '0.0.0.0']);

        $this->assertEquals('0.0.0.0', $client->getRequest()->server->get('REMOTE_ADDR'));
        $this->assertEquals('0.0.0.0', $client->getResponse()->getContent());
        $this->assertEquals('0.0.0.0', $this->getContainer()->get('request_stack')->getCurrentRequest()->getClientIp());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRequest()
     *
     * @return void
     */
    public function testGetAnotherRemoteAddress()
    {
        $client = new Client($this->getContainer());
        $client->request('GET', '/', [], [], [], [], [], [], ['REMOTE_ADDR' => '1.1.1.1']);

        $this->assertEquals('1.1.1.1', $client->getRequest()->server->get('REMOTE_ADDR'));
        $this->assertEquals('1.1.1.1', $client->getResponse()->getContent());
        $this->assertEquals('1.1.1.1', $this->getContainer()->get('request_stack')->getCurrentRequest()->getClientIp());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testGet200()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('GET', '/rest/unit/200');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testGet404()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('GET', '/rest/unit/404');

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testGetObject()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('GET', '/rest/unit/object');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testPost()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('POST', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testGetWithoutRequestAction()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('GET', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testGetWithoutRequestParameterAction()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('GET', '/rest/unit/{id}', ['id' => 5]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testGetWithRequestAndParameterAction()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('GET', '/rest/unit/request/{id}', ['id' => 5]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testGetWithQuery()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('GET', '/rest/unit/query', [], ['limit' => 150]);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testPostWithRequest()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('POST', '/rest/unit/request', [], [], ['name' => 'foo']);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testDelete()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('DELETE', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::request()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getArguments()
     *
     * @return void
     */
    public function testPut()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('PUT', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::getRouteByUri()
     *
     * @return void
     *
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @expectedExceptionMessage GET '/route/not/found' not found route ! Check your routing ;)
     */
    public function testRouteNotFound()
    {
        $client = new Client($this->getContainer());

        $response = $client->request('GET', '/route/not/found');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::__construct
     * @covers \Chaplean\Bundle\UnitBundle\Utility\Client::setCurrentRequest()
     *
     * @return void
     */
    public function testSetCurrentRequest()
    {
        $client = new Client($this->getContainer());

        $client->setCurrentRequest(Request::create('', 'GET', [], [], [], ['REMOTE_ADDR' => '82.226.243.129']));

        $this->assertEquals('82.226.243.129', $this->getContainer()->get('request_stack')->getCurrentRequest()->getClientIp());
    }
}
