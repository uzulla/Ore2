<?php
namespace MyApp;

use Ore2\Action;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SampleAction extends Action
{
    public function sample(RequestInterface $request, ResponseInterface $response)
    {
        $this->html("sample Action");
    }

}
