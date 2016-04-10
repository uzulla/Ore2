<?php
declare(strict_types=1);
namespace Ore2\Router;

use Ore2\Action;
use Ore2\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MatchAction
{
    public $container;
    public $action;

    public function __construct(Container $container, $action, array $params = [])
    {
        $this->container = $container;
        $this->container->routeParams = $params;
        $this->action = $action;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next=null):ResponseInterface
    {
        $action = $this->action;
        $methodName = "__invoke";

        if ($action instanceof \Closure) {
            $action = $action->bindTo(new Action($this->container, $request, $response));
        } else {
            if (!preg_match('|\A([\\a-zA-Z0-9_]*)::([a-zA-Z0-9_]+)\z|u', $action, $matches))
                throw new \InvalidArgumentException('invalid action string:' . $action);

            $action = new $matches[1]($this->container, $request, $response);
            $methodName = $matches[2];
        }

        $response = $action->$methodName($request, $response);

        if(is_null($next))
            $response = $next($request, $response, $next);

        return $response;
    }
}
