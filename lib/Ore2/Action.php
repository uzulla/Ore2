<?php
namespace Ore2;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Stream;

class Action
{
    /** @var Container $c */
    public $c;
    /** @var RequestInterface $request */
    public $request;
    /** @var ResponseInterface $response */
    public $response;

    public function __construct(Container $container, RequestInterface $request, ResponseInterface $response)
    {
        $this->c = $container;
        $this->request = $request;
        $this->response = $response;
    }

    public function html($html = '', $status_code = 200, ResponseInterface $response=null):ResponseInterface
    {
        return $this->raw('text/html', $html, $status_code, $response);
    }

    public function json($data, $status_code = 200, ResponseInterface $response=null):ResponseInterface
    {
        return $this->raw('application/json', json_encode($data), $status_code, $response);
    }

    public function raw($content_type=null, $data=null, $status_code = 200, ResponseInterface $response=null):ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $response ?? $this->response;
        $response = $response->withStatus($status_code);

        if(!is_null($content_type)) {
            $response = $response->withHeader('Content-Type', $content_type);
        }

        if(!is_null($data)) {
            $body = new Stream('php://memory', 'w'); // とりあえず…
            $body->write($data);
            $response = $response->withBody($body);
        }

        return $response;
    }

    public function redirect(string $url, int $status_code=302, ResponseInterface $response=null):ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $response ?? $this->response;
        $response = $response->withStatus($status_code)->withHeader('Location', $url);
        return $response;
    }
}
