<?php

namespace Chaplean\Bundle\UnitBundle\Tests;

use Chaplean\Bundle\UnitBundle\Test\LogicalTest;
use Chaplean\Bundle\UnitBundle\Utility\RestClient;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RestClientTest.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.1.0
 */
class RestClientTest extends LogicalTest
{
    /**
     * @return void
     */
    public static function setUpBeforeClass()
    {
    }

    /**
     * @return void
     */
    public function testInstantiateRestClient()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $this->assertInstanceOf(RestClient::class, $restClient);
    }

    /**
     * @return void
     */
    public function testGet200()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestGet('/rest/unit/200');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGet404()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestGet('/rest/unit/404');

        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGetObject()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestGet('/rest/unit/object');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(
            'id' => 1,
            'name' => 'foo',
        ), json_decode($response->getContent(), true));
    }

    /**
     * @return void
     */
    public function testPost()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestPost('/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testGetWithoutRequestAction()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestGet('/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array('http://:/'), json_decode($response->getContent(), true));
    }

    /**
     * @return void
     */
    public function testGetWithoutRequestParameterAction()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestGet('/rest/unit/{id}', array('id' => 5));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(5), json_decode($response->getContent(), true));
    }

    /**
     * @return void
     */
    public function testGetWithRequestAndParameterAction()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestGet('/rest/unit/request/{id}', array('id' => 5));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array('http://:/', 5), json_decode($response->getContent(), true));
    }

    /**
     * @return void
     */
    public function testGetWithQuery()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestGet('/rest/unit/query', array(), array('limit' => 150));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array(150), json_decode($response->getContent(), true));
    }

    /**
     * @return void
     */
    public function testPostWithRequest()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestPost('/rest/unit/request', array(), array(), array('name' => 'foo'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(array('foo'), json_decode($response->getContent(), true));
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestDelete('/rest/unit');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testPut()
    {
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestPut('/rest/unit');

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
        $restClient = $this->getContainer()->get('chaplean_unit.rest_client');

        $response = $restClient->requestGet('/route/not/found');

        $this->assertEquals(200, $response->getStatusCode());
    }
}

/**
 * Class UnitController.
 * @Annotations\RouteResource("Unit")
 */
class UnitController extends FOSRestController
{
    /**
     * @Annotations\Get("/unit/200")
     * @return Response
     */
    public function get200Action()
    {
        return $this->handleView($this->view(array()));
    }

    /**
     * @Annotations\Get("/unit/404")
     * @return Response
     */
    public function get404Action()
    {
        return $this->handleView($this->view(array(), 404));
    }

    /**
     * @Annotations\Get("/unit/object")
     * @return Response
     */
    public function getObjectAction()
    {
        return $this->handleView($this->view(array(
            'id' => 1,
            'name' => 'foo'
        )));
    }

    /**
     * @Annotations\Post("/unit")
     * @return Response
     */
    public function postAction()
    {
        return $this->handleView($this->view(array()));
    }

    /**
     * @param Request $request
     *
     * @Annotations\Get("/unit")
     * @return Response
     */
    public function getWithRequestAction(Request $request)
    {
        return $this->handleView($this->view(array($request->getUri())));
    }

    /**
     * @param integer $id
     *
     * @Annotations\Get("/unit/{id}")
     * @return Response
     */
    public function getWithoutRequestAction($id)
    {
        return $this->handleView($this->view(array($id)));
    }

    /**
     * @param Request $request
     * @param string  $id
     *
     * @Annotations\Get("/unit/request/{id}")
     * @return Response
     */
    public function getWithRequestAndParameterAction(Request $request, $id)
    {
        return $this->handleView($this->view(array($request->getUri(), $id)));
    }

    /**
     * @param Request $request
     *
     * @Annotations\Get("/unit/query")
     * @return Response
     */
    public function getWithQueryAction(Request $request)
    {
        return $this->handleView($this->view(array($request->query->get('limit'))));
    }

    /**
     * @param Request $request
     *
     * @Annotations\Post("/unit/request")
     * @return Response
     */
    public function postWithResquestAction(Request $request)
    {
        return $this->handleView($this->view(array($request->request->get('name'))));
    }

    /**
     * @Annotations\Delete("/unit")
     * @return Response
     */
    public function deleteAction()
    {
        return $this->handleView($this->view(array()));
    }

    /**
     * @Annotations\Put("/unit")
     * @return Response
     */
    public function putAction()
    {
        return $this->handleView($this->view(array()));
    }
}
