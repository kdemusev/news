<?php
namespace NP;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class CategoriesControllerProvider implements ControllerProviderInterface
{
  public function connect(Application $app)
  {
    $controllers = $app['controllers_factory'];

    $controllers->get('/', "NP\CategoriesControllerProvider::index")->bind('categories');
    $controllers->get('/{id}', "NP\CategoriesControllerProvider::category")
        ->assert('id', '\d+')
        ->bind('category');

    return $controllers;
  }

  function index(Application $app)
  {
    $sql = "SELECT * FROM categories ORDER BY ctname";
    $data = $app['db']->fetchAll($sql);

    return $app['twig']->render('categories.html.twig', array('categories' => $data));
  }

  function category(Application $app, $id)
  {
    $sql = "SELECT * FROM articles WHERE id IN
            (SELECT article_id FROM articlestocategories WHERE category_id = ?)";
    $articles = $app['db']->fetchAll($sql, array($id));

    $sql = "SELECT * FROM categories WHERE id = ?";
    $category = $app['db']->fetchAssoc($sql, array($id));

    return $app['twig']->render('index.html.twig',
                                array('articles' => $articles,
                                      'category' => $category));
 }


}
?>
