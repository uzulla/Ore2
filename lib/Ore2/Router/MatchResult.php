<?php
namespace Ore2\Router;

use Ore2\Container;

class MatchResult
{
    public $action;
    public $pathParams=[];

    public function __construct($action, $pathParams=[])
    {
        $this->action = $action;
        $this->pathParams = $pathParams;
    }

    public function buildAction(Container $container)
    {
        return new MatchAction($container, $this->action, $this->pathParams);
    }
}
