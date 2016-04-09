<?php
namespace Ore2;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Stream;

class Action
{

    public $c;
    /** @var  RequestInterface */
    public $request;
    /** @var  ResponseInterface */
    public $response;

    public function __construct(Container $container, RequestInterface $request, ResponseInterface $response)
    {
        $this->c = $container;
        $this->request = $request;
        $this->response = $response;
    }

    public function html($html = '', $status_code = 200)
    {
        $response = $this->response;
        $response = $response->withStatus($status_code);
        $response = $response->withHeader('Content-Type', 'text/html');
        $body = new Stream('php://memory', 'w');
        $body->write($html);
        $response = $response->withBody($body);

        $this->sendResponse($response);
    }

    public function sendResponse(ResponseInterface $response)
    {
        // Send response
        if (!headers_sent()) {
            // Status
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));
            // Headers
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }
        // Body
        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (!$body->eof()) {
            echo $body->read(1024); // TODO set nice chunk size.
        }
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        throw new \Exception('need implement');
    }

}
