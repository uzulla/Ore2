<?php
namespace Ore2;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-7対応なミドルウェアのスタックを定義し、順番に実行していく君
 * Class MiddlewareSequencer
 * @package Ore2
 */
class MiddlewareSequencer
{
    public $queue = [];

    public function __construct(array $list)
    {
        $this->queue = $list;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        $middle_ware =
            array_shift($this->queue) ??
            function (ServerRequestInterface $request, ResponseInterface $response, $this) {
                return $response;
            };
        return $middle_ware->__invoke($request, $response, $this);
    }
}
