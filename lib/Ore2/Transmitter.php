<?php
namespace Ore2;

use Psr\Http\Message\ResponseInterface;

class Transmitter
{
    /**
     * Send Response by header() and echo()
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
}
