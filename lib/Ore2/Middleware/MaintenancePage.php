<?php
declare(strict_types = 1);
namespace Ore2\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;
use Ore2\Container;
use Ore2\Session;

/**
 * メンテナンスページを表示するMiddleware
 *
 * 例
 * $middleware = new \Ore2\Middleware\MaintenancePage(
 *     function($request){
 *         return file_exists(__DIR__.'/mente.html');
 *     },
 *     __DIR__.'/mente.html'
 * )
 *
 * @package Ore2
 */
class MaintenancePage
{
    public $display_html_filepath;
    /** @var \Closure $checkFunc メンテナンス表示にすべきか真偽を返す判定関数 */
    public $checkFunc;// これは\Closureであるべきか、callableが良いか悩む

    /**
     * MaintenancePage constructor.
     * @param \Closure $check_function メンテナンス時か判定するClosure、真偽値を返すこと。
     * @param null|string $display_html_filepath メンテナンス時に表示するhtml
     */
    public function __construct(\Closure $check_function, $display_html_filepath = null)
    {
        $this->checkFunc = $check_function;
        $this->display_html_filepath = $display_html_filepath;
    }

    /**
     * middleware interface
     * @param $request
     * @param $response
     * @param $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next):ResponseInterface
    {
        if ($this->checkFunc->__invoke($request)) {
            return $this->generateMaintenancePageResponse($response);
        }

        $response = $next($request, $response);

        return $response;
    }

    /**
     * レスポンス生成
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function generateMaintenancePageResponse(ResponseInterface $response)
    {
        $body = new Stream('php://memory', 'w');
        if ($this->display_html_filepath) {
            $html = file_get_contents($this->display_html_filepath);
        } else {
            $html = 'The site is currently under maintenance.';
        }
        $body->write($html);
        $response = $response->withBody($body)->withStatus(503);
        return $response;
    }
}
