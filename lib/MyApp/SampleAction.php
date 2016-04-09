<?php
namespace MyApp;

use Ore2\Action;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SampleAction extends Action
{
    public function sample(RequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        return $this->html("sample Action");
    }

    public function sampleJson(RequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        return $this->json([
            "this is" => 'sample'
        ]);
    }

}
