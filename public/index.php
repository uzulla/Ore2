<?php
include __DIR__."/../vendor/autoload.php";

$container = new \Ore2\Container();
$container->config = require __DIR__."/../settings.php";
$container->session = new \Ore2\Session(new \Ore2\Session\Storage\PHPSession());
$container->template = new \Ore2\Template($container->config['template']);
$container->logger = new \Ore2\Logger();
// $container = new Orm

$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
$response = new \Zend\Diactoros\Response();

$router = new \Ore2\Router($container);

$router->get('/', function(){
    return $this->html('<span style="color:red">123</span>');
});

$router->get('/name/:name', function(){
    return $this->html("hello {$this->c->routeParams['name']}");
});

$router->get('/sample', '\\MyApp\\SampleAction::sample');
$router->get('/sample_json', '\\MyApp\\SampleAction::sampleJson');

/** @var \Ore2\Router\MatchAction $action */
$action = $router->findMatch($request->getMethod(), $request->getRequestTarget());

// if you need middleware, insert here.
$response = $action($request, $response);

\Ore2\Transmitter::sendResponse($response);
