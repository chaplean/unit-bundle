<?php

namespace Tests\Chaplean\Bundle\UnitBundle;

use Chaplean\Bundle\UnitBundle\Test\LogicalTestCase;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;
use Symfony\Component\HttpFoundation\Request;

/**
 * RestClientTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.coop)
 * @since     2.1.0
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
     * @return void
     */
    public function testInstantiateRestClient()
    {
        $restClient = $this->createRestClient();

        $this->assertInstanceOf(RestClient::class, $restClient);
    }

    /**
     * @return void
     */
    public function testGet200()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('GET', '/rest/unit/200');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGet404()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('GET', '/rest/unit/404');

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGetObject()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('GET', '/rest/unit/object');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(
            'id' => 1,
            'name' => 'foo',
        ), $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testPost()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('POST', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGetWithoutRequestAction()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('GET', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array('http://:/'), $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testGetWithoutRequestParameterAction()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('GET', '/rest/unit/{id}', array('id' => 5));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(5), $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testGetWithRequestAndParameterAction()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('GET', '/rest/unit/request/{id}', array('id' => 5));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array('http://:/', 5), $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testGetWithQuery()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('GET', '/rest/unit/query', array(), array('limit' => 150));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(150), $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testPostWithRequest()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('POST', '/rest/unit/request', array(), array(), array('name' => 'foo'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array('foo'), $restClient->getContent());
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('DELETE', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testPut()
    {
        $restClient = $this->createRestClient();

        $response = $restClient->request('PUT', '/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     *
     * @expectedException Symfony\Component\Routing\Exception\RouteNotFoundException
     * @expectedExceptionMessage GET '/route/not/found' not found route ! Check your routing ;)
     */
    public function testRouteNotFound()
    {
        $restClient = $this->createRestClient();

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
        $restClient = $this->createRestClient();

        $restClient->getContent();
    }

    /**
     * @return void
     */
    public function testSetCurrentRequest()
    {
        $restClient = $this->createRestClient();

        $restClient->setCurrentRequest(Request::create('', 'GET', array(), array(), array(), array('REMOTE_ADDR' => '82.226.243.129')));

        $this->assertEquals('82.226.243.129', $this->getContainer()->get('request_stack')->getCurrentRequest()->getClientIp());
    }
}
