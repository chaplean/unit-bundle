<?php
/**
 * Client.php.
 *
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     X.Y.Z
 */

namespace Chaplean\Bundle\UnitBundle\Utility;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;

/**
 * Class Client.
 *
 * @package   Chaplean\Bundle\UnitBundle\Utility
 * @author    Valentin - Chaplean <valentin@chaplean.com>
 * @copyright 2014 - 2016 Chaplean (http://www.chaplean.com)
 * @since     2.2.0
 *
 * @method Response requestDelete($uri, array $params = array(), array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
 * @method Response requestGet($uri, array $params = array(), array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
 * @method Response requestPost($uri, array $params = array(), array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
 * @method Response requestPut($uri, array $params = array(), array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
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
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'requestDelete':
                return $this->callRequest('DELETE', $arguments);
            case 'requestGet':
                return $this->callRequest('GET', $arguments);
            case 'requestPost':
                return $this->callRequest('POST', $arguments);
            case 'requestPut':
                return $this->callRequest('PUT', $arguments);
        }

        throw new BadMethodCallException(sprintf('%s method not exist !', $name));
    }

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
    }

    /**
     * @param string $type
     * @param array  $arguments
     *
     * @return mixed
     */
    public function callRequest($type, $arguments)
    {
        return call_user_func_array(array($this, 'request'), array_merge(array($type), $arguments));
    }

    /**
     * @param string $class
     * @param string $method
     * @param array  $parameters
     * @param string $type
     *
     * @return array
     */
    protected function getArguments($class, $method, $parameters, $type)
    {
        $reflectionMethod = new \ReflectionMethod($class, $method);

        list($query, $request, $attributes, $cookies, $files, $server, $content) = $this->parametersRequest;
        $args = array();
        $arguments = $reflectionMethod->getParameters();

        foreach ($arguments as $arg) {
            if ($arg->name == 'request') {
                $request = new Request($query, $request, $attributes, $cookies, $files, $server, $content);
                $request->setMethod($type);
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
    public function request($type, $uri, array $params = array(), array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        $this->setCurrentRequest(Request::create('', $type));
        $this->parametersRequest = array($query, $request, $attributes, $cookies, $files, $server, $content);
        $route = $this->getRouteByUri($type, $uri);

        $controller = $route->getDefault('_controller');
        list($class, $method) = explode('::', $controller, 2);

        $args = $this->getArguments($class, $method, $params, $type);

        /** @var Controller $controller */
        $controller = new $class();
        $controller->setContainer($this->container);

        $this->response = call_user_func_array(array($controller, $method), $args);
        return $this->response;
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
