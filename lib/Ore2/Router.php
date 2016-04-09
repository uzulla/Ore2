<?php
declare(strict_types=1);
namespace Ore2;

use Ore2\Router\MatchResult;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Router
{
    public $route = [];

    public $specialRoute = [
        "not_found" => '\Ore2\Router\DefaultRoute::notFound',
        "method_not_allowed" => '\Ore2\Router\DefaultRoute::methodNotAllowed'
    ];

    public function get(string $path, $action):self
    {
        return $this->setRoute(['get'], $path, $action);
    }

    public function post(string $path, $action):self
    {
        return $this->setRoute(['post'], $path, $action);
    }

    public function setRoute(array $method_list, string $path, $action):self
    {
        foreach($method_list as $method) {
            $method = strtolower($method);
            $this->route[$method] = $this->route[$method] ?? [];
            $this->route[$method][$path] = $action;
        }
        return $this;
    }

    public function setSpecialRoute(string $name, $action):self
    {
        $this->specialRoute[$name] = $action;
        return $this;
    }

    /**
     * @param string $method
     * @param string $path
     * @return MatchResult
     */
    public function findMatch($method = 'get', $path = '/'):MatchResult
    {
        $method = strtolower($method);

        if (!isset($this->route[$method]))
            return new MatchResult($this->specialRoute['method_not_allowed']);

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
            return new MatchResult($this->specialRoute['not_found']);

        $matches = array_map('urldecode', $matches);

        return new MatchResult($route_list[$match_path], $matches);
    }

    /**
     * Convenient execute.
     * PSR-7がどうこうがない人はこちらを使うと早い
     * @param Container $container
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    public function run(Container $container, RequestInterface $request, ResponseInterface $response)
    {
        $response = $this
            ->findMatch($request->getMethod(), $request->getRequestTarget())
            ->buildAction($container)
            ->__invoke($request, $response);
        Transmitter::sendResponse($response);
    }
}
