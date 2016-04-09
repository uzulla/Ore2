<?php
include __DIR__."/../vendor/autoload.php";

// 各種ヘルパーをコンテナにいれる
$container = new \Ore2\Container();
$container->config = require __DIR__."/../settings.php";
$container->session = new \Ore2\Session(new \Ore2\Session\Storage\PHPSession());
$container->template = new \Ore2\Template($container->config['template']);
$container->logger = new \Ore2\Logger();

// Route 登録
$router = new \Ore2\Router();
$router->get('/', function(){
    return $this->html('<span style="color:red">123</span>');
});
$router->get('/name/:name', function(){
    return $this->html("hello {$this->c->routeParams['name']}");
});
$router->get('/sample', '\\MyApp\\SampleAction::sample');
$router->get('/sample_json', '\\MyApp\\SampleAction::sampleJson');

// PSR-7オブジェクトのファクトリを実装するの諦めました
$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
$response = new \Zend\Diactoros\Response();

// route 解決
$action = $router->findMatch($container, $request->getMethod(), $request->getRequestTarget());

// 実行
$response = $action($request, $response);

// 送信
\Ore2\Transmitter::sendResponse($response);
