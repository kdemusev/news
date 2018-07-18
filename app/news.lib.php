<?php
namespace NP;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class NewsControllerProvider implements ControllerProviderInterface
{
  public function connect(Application $app)
  {
    $controllers = $app['controllers_factory'];

    $controllers->get('/', "NP\NewsControllerProvider::index")->bind('index');
    $controllers->get('/news/{id}', "NP\NewsControllerProvider::news")
        ->assert('id', '\d+')
        ->bind('single');

    return $controllers;
  }

  function index(Application $app)
  {
    $sql = "SELECT * FROM articles ORDER BY attime DESC";
    $articles = $app['db']->fetchAll($sql);

    return $app['twig']->render('index.html.twig', array('articles' => $articles));
  }

  function news(Application $app, $id)
  {
    $sql = "SELECT * FROM articles WHERE id = ?";
    $article = $app['db']->fetchAssoc($sql, array($id));

    return $app['twig']->render('single.html.twig', array('article' => $article));
 }


}
?>
