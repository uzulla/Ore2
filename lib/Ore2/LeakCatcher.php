<?php
declare(strict_types=1);
namespace Ore2;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * 下位のミドルウェアで出力をしていないか、ヘッダの送信をしていないか監視する君
 * ただし、下位でob_*を破壊されるとただしく検出はむずかしい
 *
 * Class LeakCatcher
 * @package Ore2
 */
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
            error_log('!!!Something stdout leaked!!!:' . $buffer);
        }
        if (headers_sent()) {
            error_log('!!!Something header sent!!!');
        }

        return $response;
    }
}
