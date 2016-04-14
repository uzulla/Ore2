<?php
declare(strict_types=1);
namespace Ore2;

use Ore2\Router\MatchResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * 見ての通りルーター
 * Class Router
 * @package Ore2
 */
class Router
{
    /** @var array ルート配列 */
    public $route = [];
    /** @var array 内部で使う特別なルート配列 */
    public $specialRoute = [
        "not_found" => '\Ore2\Router\DefaultRoute::notFound',
        "method_not_allowed" => '\Ore2\Router\DefaultRoute::methodNotAllowed'
    ];

    /**
     * ルートを登録
     * @param array $method_list
     * @param string $path
     * @param $action
     * @return Router
     */
    public function setRoute(array $method_list, string $path, $action):self
    {
        foreach($method_list as $method) {
            $method = strtolower($method);
            // 未定義のMethodが来た時に空配列を入れているだけ
            $this->route[$method] = $this->route[$method] ?? [];
            $this->route[$method][$path] = $action;
        }
        return $this;
    }

    public function get(string $path, $action):self
    {
        return $this->setRoute(['get'], $path, $action);
    }

    public function post(string $path, $action):self
    {
        return $this->setRoute(['post'], $path, $action);
    }

    /**
     * NotFoundなど、内部でつかう特殊なrouteを追加、上書きする
     * @param string $name
     * @param $action
     * @return Router
     */
    public function setSpecialRoute(string $name, $action):self
    {
        $this->specialRoute[$name] = $action;
        return $this;
    }

    /**
     * マッチするActionを検索して、結果のMatchResultを返す
     * @param string $method
     * @param string $path
     * @return MatchResult
     */
    public function findMatch($method = 'get', $path = '/'):MatchResult
    {
        $method = strtolower($method);

        // 存在しないMethodならMethodNotAllowedを返す
        if (!isset($this->route[$method]))
            return new MatchResult($this->specialRoute['method_not_allowed']);

        $route_list = $this->route[$method];

        // マッチ検証のためのパスパラメータキャプチャもできる正規表現生成
        $regex_list = [];
        foreach ($route_list as $route => $cb) {
            $regex_list[$route] = '#\A'.preg_replace_callback(
                '/:([\w]+)/',
                function ($m) { return "(?P<{$m[1]}>[^/]+)"; },
                $route
            ).'\z#u'; // sample -> #\A/name/(?P<name>[^/]+)\z#u
        }

        // マッチ検証、$matchesにはパスパラメータなどが入る
        $matches = [];
        $match_path = false;
        foreach ($regex_list as $_path => $regex) {
            if (preg_match($regex, $path, $matches)) {
                $match_path = $_path;
                break;
            }
        }

        // マッチしなければNotFound
        if ($match_path == false)
            return new MatchResult($this->specialRoute['not_found']);

        // パスパラメータをデコード
        $matches = array_map('urldecode', $matches);

        return new MatchResult($route_list[$match_path], $matches);
    }

    /**
     * RequestInterfaceを使う時の、findMatchのショートカット
     * @param ServerRequestInterface $request
     * @return MatchResult
     */
    public function findMatchByRequest(ServerRequestInterface $request)
    {
        return $this->findMatch($request->getMethod(), $request->getRequestTarget());
    }

    /**
     * PSR-7が不要なら、こちらを使えばクライアントに送信まで一括実行
     * @param Container $container
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function run(Container $container, ServerRequestInterface $request, ResponseInterface $response)
    {
        $response = $this
            ->findMatchByRequest($request)
            ->buildAction($container)
            ->__invoke($request, $response);
        Transmitter::sendResponse($response);
    }
}
