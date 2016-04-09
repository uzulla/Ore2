<?php
namespace Ore2\Router;

use Ore2\Action;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DefaultRoute extends Action
{
    public function notfound(RequestInterface $request, ResponseInterface $response)
    {
        $this->html("Notfound", 404);
    }

}
