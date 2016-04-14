<?php
declare(strict_types=1);
namespace Ore2\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Stream;

/**
 * このミドルウェア以下で発生した例外がこのミドルウェアまであがってきたら
 * 処理できなかったものとしてInternalServerErrorとメッセージをレスポンスする
 * Class ExceptionCatcher
 * @package Ore2
 */
class ExceptionCatcher
{
    /**
     * middle ware interface
     * @param $request
     * @param $response
     * @param $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next):ResponseInterface
    {
        try {
            $response = $next($request, $response);
        }catch(\Throwable $e){
            error_log("Uncaught Exception :{$e->getFile()}:{$e->getLine()}:{$e->getMessage()}"/*.print_r(debug_backtrace(),true)*/);
            return $this->generateServerError($this->generateServerError(($response)));
        }
        return $response;
    }

    public function generateServerError(ResponseInterface $response)
    {
        $body = new Stream('php://memory', 'w');
        $body->write('We\'re sorry.but something went wrong.');
        $response = $response->withBody($body)->withStatus(500);
        return $response;
    }

}
