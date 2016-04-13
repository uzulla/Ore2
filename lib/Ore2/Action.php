<?php
declare(strict_types=1);
namespace Ore2;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;

class Action
{
    /** @var Container $c */
    public $c;
    /** @var ServerRequestInterface $request */
    public $request;
    /** @var ResponseInterface $response */
    public $response;
    /** @var array $parsedBody */
    public $parsedBody;

    public $parsedRouteParams;

    public function __construct(Container $container, RequestInterface $request, ResponseInterface $response)
    {
        $this->c = $container;
        $this->request = $request;
        $this->response = $response;
        $this->parsedBody = $this->request->getParsedBody();
        $this->parsedRouteParams = $this->c->routeParams;
    }

    public function subRequest($method, $url, $request, $response)
    {
        /** @var Router $router */
        $router = $this->c->router;
        $match_result = $router->findMatch($method, $url);
        $action = $match_result->buildAction($this->c);
        return $action($request, $response);
    }

    public function params(string $name)
    {
        return $this->parsedBody[$name] ?? null;
    }

    public function routeParams(string $name)
    {
        return $this->parsedRouteParams[$name] ?? null;
    }

    public function render(string $template, array $params=[], int $status_code = 200, ResponseInterface $response = null):ResponseInterface
    {
        $params = array_merge($params, $this->c->viewParams);
        return $this->html($this->c->template->render($template, $params, $status_code, $response));
    }

    public function html(string $html = '', int $status_code = 200, ResponseInterface $response = null):ResponseInterface
    {
        return $this->raw('text/html', $html, $status_code, $response);
    }

    public function json($data, int $status_code = 200, ResponseInterface $response = null):ResponseInterface
    {
        return $this->raw('application/json', json_encode($data), $status_code, $response); // TODO need more nice json_encode opt
    }

    public function raw(string $content_type = null, string $data = null, int $status_code = 200, ResponseInterface $response = null):ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $response ?? $this->response;
        $response = $response->withStatus($status_code);

        if (!is_null($content_type)) {
            $response = $response->withHeader('Content-Type', $content_type);
        }

        if (!is_null($data)) {
            $body = new Stream('php://memory', 'w'); // とりあえず…
            $body->write($data);
            $response = $response->withBody($body);
        }

        return $response;
    }

    public function redirect(string $url, int $status_code = 302, ResponseInterface $response = null):ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $response ?? $this->response;
        $response = $response->withStatus($status_code)->withHeader('Location', $url);
        return $response;
    }
}
