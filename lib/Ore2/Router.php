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
        "notfound" => '\Ore2\Router\DefaultRoute::notfound'
    ];

    public $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function any(string $path, $callback)
    {
        foreach (array_keys($this->route) as $method)
            $this->route[$method][$path] = $callback;
        return $this;
    }

    public function get(string $path, $callback)
    {
        return $this->setRoute('get', $path, $callback);
    }

    public function post(string $path, $callback)
    {
        return $this->setRoute('post', $path, $callback);
    }

    public function setRoute(string $method, string $path, $callback)
    {
        $method = strtolower($method);
        if (!isset($this->route[$method]))
            throw new \InvalidArgumentException('Not acceptable method');

        $this->route[$method][$path] = $callback;
        return $this;
    }

    public function setSpecialRoute($name, $callback)
    {
        $this->specialRoute[$name] = $callback;
        return $this;
    }

    /**
     * @param string $method
     * @param string $uri
     * @return MatchAction
     */
    public function findMatch($method = 'get', $uri = '/'):MatchAction
    {
        $method = strtolower($method);

        if (!isset($this->route[$method]))
            throw new \InvalidArgumentException('Not acceptable method');

        $route_list = $this->route[$method];

        // create regex list
        $regex_list = [];
        foreach ($route_list as $route => $cb) {
            $regex_list[$route] = preg_replace_callback(
                '#:([\w]+)#',
                function ($m) {
                    return "(?P<{$m[1]}>[^/]+)";
                },
                $route
            );
        }

        // do match!
        $matches = [];
        $match_route = false;

        foreach ($regex_list as $_path => $regex) {
            if (preg_match("#\A{$regex}\z#u", $uri, $matches)) {
                $match_route = $_path;
                break;
            }
        }

        if ($match_route == false)
            return new MatchAction($this->container, $this->specialRoute['notfound']);

        // response callback
        $params = preg_grep('/[0-9]/u', $matches, PREG_GREP_INVERT);
        array_walk($params, function (&$v) {
            $v = urldecode($v);
        });

        $cb = $this->route[$method][$match_route];

        return new MatchAction($this->container, $cb, $params);
    }

    public function run(RequestInterface $request, $response)
    {
        $this
            ->findMatch($request->getMethod(), $request->getRequestTarget())
            ->__invoke($request, $response);
    }
}

class RouteNotFoundException extends \Exception
{
}