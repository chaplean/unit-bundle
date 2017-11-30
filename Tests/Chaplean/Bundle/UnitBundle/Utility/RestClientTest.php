<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Utility\RestClient;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * RestClientTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.1.0
 */
class RestClientTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testInstantiateRestClient()
    {
        $restClient = new RestClient($this->getContainer());

        $this->assertInstanceOf(RestClient::class, $restClient);
    }

    /**
     * @return void
     */
    public function testGet200()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('GET', '/rest/unit/200');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGet404()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('GET', '/rest/unit/404');

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGetObject()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('GET', '/rest/unit/object');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(
            [
            'id' => 1,
            'name' => 'foo',
            ], $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testPost()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('POST', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGetWithoutRequestAction()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('GET', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['http://:/'], $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testGetWithoutRequestParameterAction()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('GET', '/rest/unit/{id}', ['id' => 5]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([5], $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testGetWithRequestAndParameterAction()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('GET', '/rest/unit/request/{id}', ['id' => 5]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['http://:/', 5], $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testGetWithQuery()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('GET', '/rest/unit/query', [], ['limit' => 150]);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([150], $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testPostWithRequest()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('POST', '/rest/unit/request', [], [], ['name' => 'foo']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['foo'], $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('DELETE', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testPut()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('PUT', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     *
     * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
     * @expectedExceptionMessage GET '/route/not/found' not found route ! Check your routing ;)
     */
    public function testRouteNotFound()
    {
        $restClient = new RestClient($this->getContainer());

        $response = $restClient->request('GET', '/route/not/found');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Not response flush !
     */
    public function testGetContentRestClient()
    {
        $restClient = new RestClient($this->getContainer());

        $restClient->getContent();
    }

    /**
     * @return void
     */
    public function testSetCurrentRequest()
    {
        $restClient = new RestClient($this->getContainer());

        $restClient->setCurrentRequest(Request::create('', 'GET', [], [], [], ['REMOTE_ADDR' => '82.226.243.129']));

        $this->assertEquals('82.226.243.129', $this->getContainer()->get('request_stack')->getCurrentRequest()->getClientIp());
    }
}
