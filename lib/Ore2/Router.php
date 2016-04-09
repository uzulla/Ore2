<?php
namespace Ore2;

use Ore2\Router\MatchAction;
use Psr\Http\Message\RequestInterface;

class Router
{
    public $route = [
        "post" => [],
        "get" => []
    ];

    public $specialRoute = [
        "not_found" => '\Ore2\Router\DefaultRoute::notFound',
        "method_not_allowed" => '\Ore2\Router\DefaultRoute::methodNotAllowed'
    ];

    public $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function any(string $path, $action)
    {
        foreach (array_keys($this->route) as $method)
            $this->route[$method][$path] = $action;
        return $this;
    }

    public function get(string $path, $action)
    {
        return $this->setRoute('get', $path, $action);
    }

    public function post(string $path, $action)
    {
        return $this->setRoute('post', $path, $action);
    }

    public function setRoute(string $method, string $path, $action)
    {
        $method = strtolower($method);
        if (!isset($this->route[$method]))
            throw new \InvalidArgumentException('Not acceptable method');

        $this->route[$method][$path] = $action;
        return $this;
    }

    public function setSpecialRoute($name, $action)
    {
        $this->specialRoute[$name] = $action;
        return $this;
    }

    /**
     * @param string $method
     * @param string $path
     * @return MatchAction
     */
    public function findMatch($method = 'get', $path = '/'):MatchAction
    {
        $method = strtolower($method);

        if (!isset($this->route[$method]))
            return new MatchAction($this->container, $this->specialRoute['method_not_allowed']);

        $route_list = $this->route[$method];

        // create match regex list
        $regex_list = [];
        foreach ($route_list as $route => $cb) {
            $regex_list[$route] = '#\A'.preg_replace_callback(
                '/:([\w]+)/',
                function ($m) { return "(?P<{$m[1]}>[^/]+)"; },
                $route
            ).'\z#u'; // sample -> #\A/name/(?P<name>[^/]+)\z#u
        }

        // do match!
        $matches = [];
        $match_path = false;
        foreach ($regex_list as $_path => $regex) {
            if (preg_match($regex, $path, $matches)) {
                $match_path = $_path;
                break;
            }
        }

        if ($match_path == false)
            return new MatchAction($this->container, $this->specialRoute['not_found']);

        $matches = array_map('urldecode', $matches);

        return new MatchAction($this->container, $route_list[$match_path], $matches);
    }

    public function run(RequestInterface $request, $response)
    {
        $action = $this->findMatch($request->getMethod(), $request->getRequestTarget());
        $response = $action($request, $response);
        Transmitter::sendResponse($response);
    }
}
