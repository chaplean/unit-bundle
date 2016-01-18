<?php

namespace Chaplean\Bundle\UnitBundle\Utility;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;

/**
 * RestClient.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2015 Chaplean (http://www.chaplean.com)
 * @since     2.0.1
 */
class RestClient
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var RequestStack
     */
    public $requestStack;

    /**
     * @var array
     */
    private $parametersRequest;

    /**
     * RestClient constructor.
     *
     * @param ContainerInterface $container
     * @throws ClassNotFoundException
     */
    public function __construct(ContainerInterface $container)
    {
        if (!class_exists('FOS\RestBundle\FOSRestBundle')) {
            throw new ClassNotFoundException('You must have \'FOS\RestBundle\FOSRestBundle\' for use \'RestClient\'!', new \ErrorException());
        }

        $this->router = $container->get('router');
        $this->container = $container;

        $this->setCurrentRequest(Request::create('', 'GET'));
    }

    /**
     * @param string $class
     * @param string $method
     * @param array  $parameters
     *
     * @return array
     */
    protected function getArguments($class, $method, $parameters)
    {
        $reflectionMethod = new \ReflectionMethod($class, $method);

        list($query, $request, $attributes, $cookies, $files, $server, $content) = $this->parametersRequest;
        $args = array();
        $arguments = $reflectionMethod->getParameters();

        foreach ($arguments as $arg) {
            if ($arg->name == 'request') {
                $args[] = new Request($query, $request, $attributes, $cookies, $files, $server, $content);
            } else {
                if (isset($parameters[$arg->name])) {
                    $args[] = $parameters[$arg->name];
                }
            }
        }

        return $args;
    }

    /**
     * @return mixed|null
     *
     * @throws \Exception
     */
    public function getContent()
    {
        if (empty($this->response)) {
            throw new \Exception('Not response flush !');
        }

        return json_decode($this->response->getContent(), true);
    }

    /**
     * @param string $type
     * @param string $uri
     *
     * @return Route
     */
    protected function getRouteByUri($type, $uri)
    {
        $routes = $this->router->getRouteCollection();
        $it = $routes->getIterator();

        while ($it->valid()) {
            /** @var Route $route */
            $route = $it->current();

            if ($route->getPath() == $uri && strtolower($type) == strtolower($route->getMethods()[0])) {
                return $route;
            }

            $it->next();
        }

        throw new RouteNotFoundException(sprintf('%s \'%s\' not found route ! Check your routing ;)', strtoupper($type), $uri));
    }

    /**
     * @param string $type
     * @param string $uri
     * @param array  $params
     * @param array  $query
     * @param array  $request
     * @param array  $attributes
     * @param array  $cookies
     * @param array  $files
     * @param array  $server
     * @param string $content
     *
     * @return Response
     */
    public function request($type, $uri, array $params = array(), array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        $this->parametersRequest = array($query, $request, $attributes, $cookies, $files, $server, $content);
        $route = $this->getRouteByUri($type, $uri);

        $controller = $route->getDefault('_controller');
        list($class, $method) = explode('::', $controller, 2);

        $args = $this->getArguments($class, $method, $params);

        /** @var Controller $controller */
        $controller = new $class();
        $controller->setContainer($this->container);

        $this->response = call_user_func_array(array($controller, $method), $args);
        return $this->response;
    }

    /**
     * @see RestClient::request
     * @return Response
     */
    public function requestDelete()
    {
        return call_user_func_array(array($this, 'request'), array_merge(array('DELETE'), func_get_args()));
    }

    /**
     * @see RestClient::request
     * @return Response
     */
    public function requestGet()
    {
        return call_user_func_array(array($this, 'request'), array_merge(array('GET'), func_get_args()));
    }

    /**
     * @see RestClient::request
     * @return Response
     */
    public function requestPost()
    {
        return call_user_func_array(array($this, 'request'), array_merge(array('POST'), func_get_args()));
    }

    /**
     * @see RestClient::request
     * @return Response
     */
    public function requestPut()
    {
        return call_user_func_array(array($this, 'request'), array_merge(array('PUT'), func_get_args()));
    }

    /**
     * @param Request $request
     *
     * @return RequestStack
     */
    public function setCurrentRequest(Request $request)
    {
        $this->request  = $request;
        $this->request->setRequestFormat('json');

        $this->requestStack = \Mockery::mock('Symfony\Component\HttpFoundation\RequestStack');
        $this->requestStack->shouldReceive('getCurrentRequest')->andReturn($this->request);
        $this->container->set('request_stack', $this->requestStack);
    }
}
