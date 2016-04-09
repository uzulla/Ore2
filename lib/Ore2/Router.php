<?php
namespace Ore2;

use Psr\Http\Message\RequestInterface;

class RouteNotFoundException extends \Exception
{
}

class Router
{
    public $route = [
        "post" => [],
        "get" => []
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
    }

    public function get(string $path, $callback)
    {
        $this->setRoute('get', $path, $callback);
    }

    public function post(string $path, $callback)
    {
        $this->setRoute('post', $path, $callback);
    }

    public function setRoute(string $method, string $path, $callback)
    {
        $method = strtolower($method);
        if (!isset($this->route[$method]))
            throw new \InvalidArgumentException('Not acceptable method');

        $this->route[$method][$path] = $callback;
    }

    /**
     * @param RequestInterface $request
     * @return RouterResult
     * @throws RouteNotFoundException
     */
    public function run(RequestInterface $request):RouterResult
    {
        $method = strtolower($request->getMethod());
        $uri = $request->getRequestTarget();

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

        if ($match_route == false) throw new RouteNotFoundException('any match route found.');


        // response callback
        $params = preg_grep('/[0-9]/u', $matches, PREG_GREP_INVERT);
        array_walk($params, function (&$v) {
            $v = urldecode($v);
        });

        $cb = $this->route[$method][$match_route];

        return new RouterResult($this->container, $cb, $params);
    }

    public function __invoke(RequestInterface $request)
    {
        $this->run($request);
    }
}

class RouterResult
{
    public $container;
    public $action;

    public function __construct(Container $container, $action, array $params)
    {
        $this->container = $container;
        $this->container->routeParams = $params;
        $this->action = $action;
    }

    public function __invoke($request, $response)
    {
        $action = $this->action;

        if ($this->action instanceof \Closure) {
            $action = $action->bindTo(new Action($this->container, $request, $response));
        } else if ($this->action instanceof Action) {
            // ok
        } else {
            // クラス名で起動とかやりたいよね
            throw new \InvalidArgumentException('not implemented yet');
        }

        $action->__invoke($request, $response);
    }

}
