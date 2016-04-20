<?php
declare(strict_types=1);
namespace Ore2\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;
use Ore2\Container;
use Ore2\Session;

/**
 * SessionにCSRF Tokenを保存し、Postアクセス時に確認。違反していればエラーレスポンスを生成して処理を中断する。
 * Class CsrfTrap
 * @package Ore2
 */
class CsrfTrap
{
    /** @var Container */
    public $container;
    public $csrfTokenName = '__CSRF_TOKEN__';

    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * middle ware interface
     * @param $request
     * @param $response
     * @param $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next):ResponseInterface
    {
        /** @var Session $session */
        $session = $this->container->session;
        $token_name = $this->csrfTokenName;

        $session_csrf_token = $session[$token_name] ?? false;

        if($request->getMethod()==='POST'){
            if($session_csrf_token === false){
                return $this->generateBadResponse($response);
            }

            $params = $request->getParsedBody();
            $csrf_token = $params[$token_name] ?? false;

            if($session_csrf_token!==$csrf_token){
                return $this->generateBadResponse($response);
            }
        }

        if($session_csrf_token === false){
            $session[$token_name] = bin2hex(random_bytes(64));
        }

        $this->container->viewParams = array_merge(
            $this->container->viewParams,
            [
                'csrf_token_name' => $token_name,
                'csrf_token_value' => $session[$token_name]
            ]
        );

        $response = $next($request, $response);

        return $response;
    }

    /**
     * エラー時のレスポンス生成
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function generateBadResponse(ResponseInterface $response)
    {
        $body = new Stream('php://memory', 'w');
        $body->write('invalid csrf token');
        $response = $response->withBody($body)->withStatus(400);
        return $response;
    }
}
