<?php
declare(strict_types=1);
namespace Ore2;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class LeakCatcher
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
        ob_start();
        $response = $next($request, $response);
        $buffer = ob_get_clean();
        if(strlen($buffer)){
            error_log('!!!Something leaked!!!:'.$buffer);
        }
        if (headers_sent()) {
            error_log('!!!Something header sent!!!');
        }
        return $response;
    }
}
