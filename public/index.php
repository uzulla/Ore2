<?php
include __DIR__."/../vendor/autoload.php";

$container = new \Ore2\Container();
$container->config = require __DIR__."/../settings.php";
$container->session = new \Ore2\Session(new \Ore2\Session\Storage\PHPSession());
$container->template = new \Ore2\Template($container->config['template']);
$container->logger = new \Ore2\Logger();
$container->router = new \Ore2\Router($container);
// $container = new Orm

///** @var \Ore2\Router $router */
//$router = new \Ore2\Router($container);

$container->router->get('/', function(){
    $this->html('<span style="color:red">123</span>');
});

$container->router->get('/name/:name', function(){
    $this->html("hello {$this->c->routeParams['name']}");
});

$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
$response = new \Zend\Diactoros\Response();

$action = $container->router->run($request);

$action($request, $response);



