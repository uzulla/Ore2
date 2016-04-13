<?php
declare(strict_types=1);
include __DIR__."/../vendor/autoload.php";

// Route 登録
$router = new \Ore2\Router();

$router->get('/',            '\\MyApp\\Action\\Post::showList');
$router->get('/post/:id',    '\\MyApp\\Action\\Post::show');
$router->post('/post/create', '\\MyApp\\Action\\Post::create');

$router->get('/json_sample', function(){
    return $this->json(['this'=>'is', 'json'=>'data']);
});

// 各種ヘルパーをコンテナにいれる
$container = new \Ore2\Container();
$container->config = require __DIR__."/../settings.php";
$container->router = $router;
$container->session = new \Ore2\Session(new \Ore2\Session\Storage\PHPSession());
$container->logger = new \Ore2\Logger();
$container->template =
    new Twig_Environment(
        new Twig_Loader_Filesystem($container->config['template']['template_dir']),
        ['debug' => true]
    );
$container->viewParams = [];
\MyApp\Db::$pdo = new \PDO($container->config['db']['dsn']);

// PSR-7オブジェクトのファクトリを実装するの諦めました
$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
$response = new \Zend\Diactoros\Response();
// route 解決
$match_result = $router->findMatch($request->getMethod(), $request->getRequestTarget());
// アクションを作成
$action = $match_result->buildAction($container);

// Middlewareとして実行
$seqencer = new \Ore2\MiddlewareSequencer([
    new \Ore2\Transmitter(),
    new \Ore2\ExceptionCatcher(),
    new \Ore2\LeakCatcher(),
    new \Ore2\CsrfTrap($container),
    $action
]);

$seqencer($request, $response);
