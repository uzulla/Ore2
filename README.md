# Ore2 Framework

This is NOT for production use.

Just hobby project.

## 概要

- PSR-7対応風なフレームワークサンプル

## router

```php
$router = new \Ore2\Router();

## ルート登録
# / => 引数のclosureがコール
$router->get('/', function(){
    return $this->html('<span style="color:red">123</span>');
});

# /name/uzulla => 引数のクロージャがコールされ、コンテナには引数が
$router->get('/name/:name', function(){
    return $this->html("hello {$this->c->routeParams['name']}");
});

# クラス名での設定
$router->get('/sample', '\\MyApp\\SampleAction::sample');

## ルーティング実行
$method = 'get';
$path = '/sample';
$match_result = $router->findMatch($method, $path);

# containerと合成してアクションを生成（action節を参照してください）
$action = $match_result->buildAction($container);

## アクションを実行して結果（ResponseInterface）取得
$psr7_response = $action($psr7_request, $psr7_response);
// responseをブラウザに送信する箇所はTransmitterを参照のこと

// $actionは https://github.com/relayphp/Relay.Relay と互換性

## 特にこまかい事を考えたくない場合、以下でもOK
$router = new \Ore2\Router();
$router->get('/', function(){
    return $this->html('<span style="color:red">123</span>');
});
$router->run($container, $request, $response);

## Middlewareとして実行例
$seqencer = new \Ore2\MiddlewareSequencer([
    new \Ore2\Transmitter(),
    $action
]);
$seqencer($request, $response);

```

# TBD
