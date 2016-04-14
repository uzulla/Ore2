<?php
namespace MyApp\Action;

use Ore2\Action;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * サンプルアプリ
 * Class Post
 * @package MyApp\Action
 */
class Post extends Action
{
    public function showList(ServerRequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        $m_post = new \MyApp\Db\Post();
        $list = $m_post->getAll();
        return $this->render("list.twig", ['list'=>$list]);
    }

    public function create(ServerRequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        $name = $this->params('name');
        $text = $this->params('text');

        $m_post = new \MyApp\Db\Post();
        $m_post->insert($name, $text);

        return $this->redirect('/');
    }

    public function show(ServerRequestInterface $request, ResponseInterface $response):ResponseInterface
    {
        $id = $this->routeParams('id');

        $m_post = new \MyApp\Db\Post();
        $row = $m_post->get($id);
        if($row===false){
            // 取得できなかったので、サブリクエストで404を返す
            return $this->subRequest('get', '#notfound', $request, $response);
        }

        return $this->render("show.twig", ['row'=>$row]);
    }
}
