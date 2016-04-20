<?php
declare(strict_types = 1);
include __DIR__ . "/../vendor/autoload.php";

// Route 登録
$router = new \Ore2\Router();
$router->get('/', '\\MyApp\\Action\\Post::showList');
$router->get('/post/:id', '\\MyApp\\Action\\Post::show');
$router->post('/post/create', '\\MyApp\\Action\\Post::create');
$router->get('/reset', '\\MyApp\\Action\\Post::reset');
$router->get('/sample', function ($req, $res) {
    /** @var \Ore2\Action $this Actionがbindされているので、methodつかえます */
    return $this->html('<span style="color:red">テスト</span>');
});

// 各種をコンテナにいれる
$container = new \Ore2\Container();
$container->config = require __DIR__ . "/../settings.php";
$container->router = $router;
$container->session = new \Ore2\Session(new \Ore2\Session\Storage\PHPSession());
$container->logger = new \Ore2\Logger();
$container->viewParams = [];
$container->template =
    new Twig_Environment(
        new Twig_Loader_Filesystem($container->config['template']['template_dir']),
        ['debug' => true]
    );
\MyApp\Db::$pdo = new \PDO($container->config['db']['dsn']);

// PSR-7インスタンスを自前実装するの諦めました
$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
$response = new \Zend\Diactoros\Response();

// アクションを作成
$action = $router
    ->findMatchByRequest($request)
    ->buildAction($container);

// Middlewareのスタックを定義
$seqencer = new \Ore2\MiddlewareSequencer([
    new \Ore2\Middleware\Transmitter(),
    new \Ore2\Middleware\MaintenancePage(function ($request) {
        return file_exists(__DIR__ . '/mente.html');
    },
        __DIR__ . '/mente.html'),
    new \Ore2\Middleware\ExceptionCatcher(),
    new \Ore2\Middleware\LeakCatcher(),
    new \Ore2\Middleware\RequestAndResponseCapture(__DIR__ . "/../tmp"),
    new \Ore2\Middleware\CsrfTrap($container),
    $action
]);

// Middlewareとして実行
$seqencer($request, $response);
