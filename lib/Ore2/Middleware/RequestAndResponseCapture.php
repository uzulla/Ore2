<?php
declare(strict_types = 1);
namespace Ore2\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Request;

/**
 * Zend\Diactoros\ServerRequestFactoryをもう一度つくるための$_*や、レスポンスをjsonで書き出す
 * ただし、$_FILESはまだサポートしていない
 * PSR-7はStreamを多用するし、$_FILESは勿論ファイルを書き出してあるわけで、うーん。
 *
 * Class LeakCatcher
 * @package Ore2
 */
class RequestAndResponseCapture
{
    public $outputDir;
    public $prefix = "capture_";

    public function __construct($output_dir = null)
    {
        if (is_null($output_dir))
            $this->outputDir = sys_get_temp_dir();
        else
            $this->outputDir = $output_dir;

        $this->prefix = $this->outputDir . "/" . $this->prefix;
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
        $this->prefix .= (string)(microtime(true) * 10000);

        $this->saveJsonSerializedGlobals();

        $response = $next($request, $response);

        $this->saveJsonSerializedResponse($response);

        return $response;
    }

    public function saveJsonSerializedResponse(ResponseInterface $response)
    {
        $stream = $response->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        $body = '';
        while (!$stream->eof()) {
            $body .= $stream->read(1024);
        }

        $data = [
            'status' => $response->getStatusCode(),
            'header' => $response->getHeaders(),
            'body' => $body,
        ];

        file_put_contents($this->prefix . "_response.json", json_encode($data, JSON_PRETTY_PRINT));
    }

    public function saveJsonSerializedGlobals()
    {
        $data = [
            'server' => $_SERVER,
            'query' => $_GET,
            'body' => $_POST,
            'cookie' => $_COOKIE,
            '_session' => $_SESSION,
            'files' => [] // not implemented yet.
        ];

        file_put_contents($this->prefix . "_request.json", json_encode($data, JSON_PRETTY_PRINT));
    }


}
