<?php
declare(strict_types=1);
namespace Ore2;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Transmitter
{
    /**
     * Send HTTP Response by header() and echo()
     * @param ResponseInterface|null $response
     */
    static function sendResponse(ResponseInterface $response = null)
    {
        if (!headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));

            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }

        $body = $response->getBody();

        if ($body->isSeekable()) $body->rewind();

        while (!$body->eof()) echo $body->read(1024); // TODO set nice chunk size.
    }

    /**
     * middle ware interface
     * @param $request
     * @param $response
     * @param $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next):ResponseInterface
    {
        $response = $next($request, $response);
        static::sendResponse($response);
        return $response;
    }
}
