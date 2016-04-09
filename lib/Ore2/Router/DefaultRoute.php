<?php
namespace Ore2\Router;

use Ore2\Action;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DefaultRoute extends Action
{
    public function notFound(RequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        return $this->html("NotFound", 404);
    }

    public function badRequest(RequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        return $this->html("BadRequest", 400);
    }

}
