<?php
declare(strict_types=1);
namespace Ore2\Router;

use Ore2\Container;

/**
 * ルーターから返される、マッチしたアクション（文字列あるいはクロージャ）とパスパラメータの一時的な入れ物
 * Class MatchResult
 * @package Ore2\Router
 */
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
