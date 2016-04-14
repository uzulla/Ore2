<?php
declare(strict_types=1);
namespace Ore2\Router;

use Ore2\Action;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * デフォルトで用意してある404などのアクション
 * Class DefaultRoute
 * @package Ore2\Router
 */
class DefaultRoute extends Action
{
    public function notFound(ServerRequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        return $this->html("NotFound", 404);
    }

    public function methodNotAllowed(ServerRequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        return $this->html("Method Not Allowed", 405);
    }
}
