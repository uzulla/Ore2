<?php
declare(strict_types=1);
namespace Ore2\Router;

use Ore2\Action;
use Ore2\Container;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * MatchResultとContainerを結びつけて、PSR-7なMiddlewareでつかえるAction
 * Class MatchAction
 * @package Ore2\Router
 */
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

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null):ResponseInterface
    {
        $action = $this->action;
        $methodName = "__invoke";

        // Actionの生成
        if ($action instanceof \Closure) {
            // Closureならば、ActionとbindToして、Closure内部で$thisがつかえるようにする
            $action = $action->bindTo(new Action($this->container, $request, $response));
        } else {
            if (!preg_match('|\A([\\a-zA-Z0-9_]*)::([a-zA-Z0-9_]+)\z|u', $action, $matches))
                throw new \InvalidArgumentException('invalid action string:' . $action);

            $action = new $matches[1]($this->container, $request, $response);
            $methodName = $matches[2];
        }

        // Actionの実行
        $response = $action->$methodName($request, $response);

        if(!is_null($next))
            $response = $next($request, $response, $next);

        return $response;
    }
}

// これはRouterの下ではなく、Actionの下に行くべきなのでは…
