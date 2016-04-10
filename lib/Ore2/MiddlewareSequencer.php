<?php
namespace Ore2;

use Psr\Http\Message\ResponseInterface;

class MiddlewareSequencer
{
    public $queue = [];

    public function __construct(array $list)
    {
        $this->queue = $list;
    }

    public function __invoke($a, $b):ResponseInterface
    {
        $_ = array_shift($this->queue);
        $_ = $_ ?? function($a, $b, $this){ return $b; };
        return $_->__invoke($a, $b, $this);
    }
}
