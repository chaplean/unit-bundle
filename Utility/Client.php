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
 * Class Client.
 *
 * @package   Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.coop>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.coop)
 * @since     2.2.0
 */
class Client
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var RequestStack
     */
    public $requestStack;

    /**
     * @var array
     */
    protected $parametersRequest;

    /**
     * RestClient constructor.
     *
     * @param ContainerInterface $container
     *
     * @throws ClassNotFoundException
     */
    public function __construct(ContainerInterface $container)
    {
        // @codeCoverageIgnoreStart
        if (!class_exists('FOS\RestBundle\FOSRestBundle')) {
            throw new ClassNotFoundException('You must have \'FOS\RestBundle\FOSRestBundle\' for use \'RestClient\'!', new \ErrorException());
        }
        // @codeCoverageIgnoreEnd

        $this->router = $container->get('router');
        $this->container = $container;
    }

    /**
     * @param string $class
     * @param string $method
     * @param array  $parameters
     * @param string $type
     *
     * @return array
     */
    protected function getArguments($class, $method, array $parameters, $type)
    {
        $reflectionMethod = new \ReflectionMethod($class, $method);

        $request = new Request(...$this->parametersRequest);
        $request->setMethod($type);

        $this->setCurrentRequest($request);

        $args = [];
        $arguments = $reflectionMethod->getParameters();

        foreach ($arguments as $arg) {
            if ($arg->name == 'request') {
                $args[] = $request;
            } else {
                if (isset($parameters[$arg->name])) {
                    $args[] = $parameters[$arg->name];
                }
            }
        }

        return $args;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
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
     * @see Request::create()
     *
     * @param string $type       Method of request (delete, get, post, put)
     * @param string $uri        The URI to fetch with parameters name (example: /foo/{bar})
     * @param array  $params     Array of values of parameters in URI (example array('bar' => 1))
     * @param array  $query      Parameters passing in query
     * @param array  $request    Parameters passing in request
     * @param array  $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array  $cookies    The request cookies ($_COOKIE)
     * @param array  $files      The request files ($_FILES)
     * @param array  $server     The server parameters ($_SERVER)
     * @param string $content    The raw body data
     *
     * @return Response
     */
    public function request(
        $type,
        $uri,
        array $params = [],
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        $this->parametersRequest = [$query, $request, $attributes, $cookies, $files, $server, $content];
        $route = $this->getRouteByUri($type, $uri);

        $controller = $route->getDefault('_controller');
        list($class, $method) = explode('::', $controller, 2);

        $args = $this->getArguments($class, $method, $params, $type);

        /** @var Controller $controller */
        $controller = new $class();
        $controller->setContainer($this->container);

        $this->response = $controller->$method(...$args);

        return $this->response;
    }

    /**
     * @param Request $request
     *
     * @return void
     */
    public function setCurrentRequest(Request $request)
    {
        $this->request = $request;
        $this->request->setRequestFormat('json');
        $this->requestStack = \Mockery::mock('Symfony\Component\HttpFoundation\RequestStack');
        $this->requestStack->shouldReceive('getCurrentRequest')->andReturn($this->request);
        $this->requestStack->shouldReceive('getMasterRequest')->andReturn($this->request);

        $this->container->set('request_stack', $this->requestStack);
    }
}
