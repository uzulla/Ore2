<?php
namespace Ore2\Test;
use Ore2\Container;
use Ore2\Router;

class routerText extends \PHPUnit_Framework_TestCase
{
    const dateRegexFormat = '\[[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\]';

    public function testAddPath()
    {
        $router = new \Ore2\Router();
        $this->assertEquals(0, count($router->route));

        $router->get('/', function(){
            return $this->raw();
        });

        $this->assertEquals(1, count($router->route));
    }

    public function testFind()
    {
        $router = new \Ore2\Router();

        $action = function(){
            return $this->raw();
        };

        $router->get('/', $action);

        $match = $router->findMatch('get', '/');

        $this->assertEquals($action, $match->action);
    }

    public function testNotfound()
    {
        $router = new \Ore2\Router();

        $action = function(){
            return $this->raw();
        };

        $router->get('/', $action);

        $match = $router->findMatch('get', '/something');

        $notfound_action = $router->specialRoute['not_found'];

        $this->assertEquals($notfound_action, $match->action);
    }

    public function testPathParams()
    {
        $router = new \Ore2\Router();

        $action = function(){
            return $this->raw();
        };

        $router->get('/:test', $action);
        $router->get('/test/:test', $action);
        $router->get('/test/:test/test2/:test2', $action);

        $match = $router->findMatch('get', '/something');
        $this->assertEquals('something', $match->pathParams['test'] );

        $match = $router->findMatch('get', '/test/hoge');
        $this->assertEquals('hoge', $match->pathParams['test'] );

        $match = $router->findMatch('get', '/test/hoge/test2/fugo');
        $this->assertEquals('hoge', $match->pathParams['test'] );
        $this->assertEquals('fugo', $match->pathParams['test2'] );
    }

    public function testBuildAction()
    {
        $router = new \Ore2\Router();

        $action = function(){
            return $this->raw();
        };

        $router->get('/', $action);
        $match = $router->findMatch('get', '/something');
        $action = $match->buildAction(new Container());

        $this->assertInstanceOf('Ore2\Router\MatchAction', $action);

        $request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
        $response = new \Zend\Diactoros\Response();

        $response = $action->__invoke($request, $response);
        $this->assertInstanceOf('Psr\Http\Message\ResponseInterface', $response);

    }

    public function testRun()
    {
        $router = new \Ore2\Router();

        $action = function(){
            return $this->raw('text/plain', '1');
        };

        $router->get('/', $action);
        $match = $router->findMatch('get', '/');
        $action = $match->buildAction(new Container());

        $request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
        $response = new \Zend\Diactoros\Response();

        $response = $action->__invoke($request, $response);

        $body = $response->getBody();
        $body->rewind();
        $str = $body->read(128);

        $this->assertEquals("1", $str);
    }

}