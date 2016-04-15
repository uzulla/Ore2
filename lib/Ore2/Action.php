<?php
declare(strict_types=1);
namespace Ore2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;

/**
 * Actionのベースクラス
 * 各種ヘルパ、ショートカット
 *
 * Class Action
 * @package Ore2
 */
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

    public function __construct(Container $container, ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->c = $container;
        $this->request = $request;
        $this->response = $response;
        $this->parsedBody = $this->request->getParsedBody();
        $this->parsedRouteParams = $this->c->routeParams;
    }

    /**
     * ルーターをもう一度回し、新たなアクションを処理する
     * 内部リダイレクトや、404生成などにつかう
     * @param $method
     * @param $url
     * @param $request
     * @param $response
     * @return mixed
     */
    public function subRequest($method, $url, $request, $response)
    {
        /** @var Router $router */
        $router = $this->c->router;
        $match_result = $router->findMatch($method, $url);
        $action = $match_result->buildAction($this->c);
        return $action($request, $response);
    }

    /**
     * PSR-7のparsedBodyへのショートカット
     * @param string $name
     * @return null
     */
    public function params(string $name)
    {
        return $this->parsedBody[$name] ?? null;
    }

    /**
     * ルーターでキャプチャされたパスパラメータ
     * @param string $name
     * @return null
     */
    public function routeParams(string $name)
    {
        return $this->parsedRouteParams[$name] ?? null;
    }

    /**
     * テンプレートエンジンをつかってHTMLを生成し、レスポンスオブジェクトを生成
     * @param string $template
     * @param array $params
     * @param int $status_code
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function render(string $template, array $params=[], int $status_code = 200, ResponseInterface $response = null):ResponseInterface
    {
        $params = array_merge($params, $this->c->viewParams);
        return $this->html($this->c->template->render($template, $params), $status_code, $response);
    }

    /**
     * 文字列をHTMLとしてレスポンスオブジェクト生成
     * @param string $html
     * @param int $status_code
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function html(string $html = '', int $status_code = 200, ResponseInterface $response = null):ResponseInterface
    {
        return $this->raw('text/html', $html, $status_code, $response);
    }

    /**
     * 渡されたオブジェクトをjson_encodeしてレスポンスオブジェクト生成
     * @param $data
     * @param int $status_code
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function json($data, int $status_code = 200, ResponseInterface $response = null):ResponseInterface
    {
        return $this->raw('application/json', json_encode($data), $status_code, $response); // TODO need more nice json_encode opt
    }

    /**
     * レスポンスオブジェクトを生成
     * @param string|null $content_type
     * @param string|null $data
     * @param int $status_code
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
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

    /**
     * リダイレクトのレスポンスオブジェクトを生成
     * @param string $url
     * @param int $status_code
     * @param ResponseInterface|null $response
     * @return ResponseInterface
     */
    public function redirect(string $url, int $status_code = 302, ResponseInterface $response = null):ResponseInterface
    {
        /** @var ResponseInterface $response */
        $response = $response ?? $this->response;
        $response = $response->withStatus($status_code)->withHeader('Location', $url);
        return $response;
    }
}
