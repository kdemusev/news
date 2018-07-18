<?php
namespace NP;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class AdminControllerProvider implements ControllerProviderInterface
{
  public function connect(Application $app)
  {
    $controllers = $app['controllers_factory'];

    $controllers->get('/', "NP\AdminControllerProvider::index")->bind('admin');
    $controllers->get('/logout', "NP\AdminControllerProvider::logout")->bind('admin/logout');
    $controllers->get('/categories', "NP\AdminControllerProvider::categories")->bind('admin/categories');
    $controllers->post('/categories/save', "NP\AdminControllerProvider::categoriesSave");
    $controllers->get('/news', "NP\AdminControllerProvider::news")->bind('admin/news');
    $controllers->get('/news/add', "NP\AdminControllerProvider::addnews");
    $controllers->post('/news/add/save', "NP\AdminControllerProvider::addnewssave");
    $controllers->get('/news/del/{id}', "NP\AdminControllerProvider::delnews");
    $controllers->get('/news/del/confirm/{id}', "NP\AdminControllerProvider::delnewsconfirm");
    $controllers->get('/news/edit/{id}', "NP\AdminControllerProvider::editnews");
    $controllers->post('/news/edit/save', "NP\AdminControllerProvider::editnewssave");
    $controllers->post('/login', "NP\AdminControllerProvider::login");

    return $controllers;
  }

  function login(Request $r, Application $app)
  {
    $usname = $r->get('usname');
    $uspassword = $r->get('uspassword');
    $usremember = $r->get('usremember');

    $sql = "SELECT COUNT(*) AS c FROM settings WHERE usname = ? AND uspassword=?";
    $res = $app['db']->fetchAll($sql, array($usname, $uspassword));

    if($res[0]['c'] == 0) {
      return $app['twig']->render('admin.login.html.twig',
                                  array(
                                    'message' => 'loginerror',
                                  ));
    }

    $app['session']->set('uslogged', 1);
    return $app->redirect('./');
  }

  function index(Application $app)
  {
    if($app['session']->get('uslogged')===null) {
      return $app['twig']->render('admin.login.html.twig');
    }

    return $app->redirect('news/');;
  }

  static function logout(Application $app) {
    $app['session']->set('uslogged', null);
    return $app->redirect('../');
  }

  function categories(Application $app) {
    $sql = "SELECT * FROM categories ORDER BY ctname";
    $res = $app['db']->fetchAll($sql);

    return $app['twig']->render('admin.categories.html.twig', array('categories' => $res));
  }

  function categoriesSave(Request $r, Application $app) {
    $ctname = $r->get('ctname');
    $add_ctname = $r->get('add_ctname');
    $cttodel = $r->get('cttodel');

    foreach($ctname as $key => $rec) {
      $sql = "UPDATE categories SET ctname = ? WHERE id = ?";
      $app['db']->executeQuery($sql, array($rec, $key));
    }

    foreach($add_ctname as $rec) {
      if(trim($rec) == '') {
        continue;
      }
      $sql = "INSERT INTO categories(ctname) VALUES(?)";
      $app['db']->executeQuery($sql, array($rec));
    }

    foreach($cttodel as $rec) {
      $sql = "DELETE FROM categories WHERE id = ?";
      $app['db']->executeQuery($sql, array($rec));
    }

    return $app->redirect('../categories');
  }

  function news(Application $app)
  {
    $sql = "SELECT * FROM articles ORDER BY attime DESC";
    $res = $app['db']->fetchAll($sql);

    return $app['twig']->render('admin.news.html.twig', array('news' => $res));
  }

  function addnews(Application $app) {
    $sql = "SELECT * FROM categories ORDER BY ctname";
    $res = $app['db']->fetchAll($sql);

    return $app['twig']->render('admin.news.add.html.twig', array('categories' => $res));
  }

  function addnewsSave(Request $r, Application $app) {
    $atname = $r->get('atname');
    $atcontents = $r->get('atcontents');
    $atcategories = $r->get('atcategories');
    $atauthor = $r->get('atauthor');
    $attime = time();

    $sql = "INSERT INTO articles(atname, atcontents, attime, atauthor) VALUES(?, ?, ?, ?)";
    $app['db']->executeQuery($sql, array($atname, $atcontents, $attime, $atauthor));

    $article_id = $app['db']->lastInsertId();

    foreach($atcategories as $rec) {
      $sql = "INSERT INTO articlestocategories(article_id, category_id)
              VALUES(?, ?)";
      $app['db']->executeQuery($sql, array($article_id, $rec));
    }

    return $app->redirect('../');
  }

  function delnews($id, Application $app) {
    $sql = "SELECT * FROM articles WHERE id = ?";
    $res = $app['db']->fetchAssoc($sql, array($id));

    return $app['twig']->render('admin.news.del.html.twig', array('data' => $res));
  }

  function delnewsconfirm($id, Application $app) {
    $sql = "DELETE FROM articles WHERE id = ?";
    $app['db']->executeQuery($sql, array($id));

    $sql = "DELETE FROM articlestocategories WHERE article_id NOT IN
            (SELECT id FROM articles)";
    $app['db']->executeQuery($sql);

    return $app->redirect('../../');
  }

  function editnews($id, Application $app) {
    $sql = "SELECT * FROM articles WHERE id = ?";
    $article = $app['db']->fetchAssoc($sql, array($id));

    $sql = "SELECT * FROM categories ORDER BY ctname";
    $categories = $app['db']->fetchAll($sql);

    $sql = "SELECT * FROM articlestocategories WHERE article_id = ?";
    $atoc = $app['db']->fetchAll($sql, array($id));

    $atocdata = array();
    foreach($atoc as $rec) {
      $atocdata[] = $rec['category_id'];
    }

    foreach($categories as $key => $rec) {
      $categories[$key]['selected'] = in_array($rec['id'], $atocdata) ? 1 : 0;
    }

    return $app['twig']->render('admin.news.edit.html.twig',
                                array('data' => $article,
                                      'categories' => $categories));
  }

  function editnewsSave(Request $r, Application $app) {
    $id = $r->get('id');
    $atname = $r->get('atname');
    $atcontents = $r->get('atcontents');
    $atcategories = $r->get('atcategories');
    $atauthor = $r->get('atauthor');

    $sql = "UPDATE articles SET atname = ?, atcontents = ?, atauthor = ? WHERE id = ?";
    $app['db']->executeQuery($sql, array($atname, $atcontents, $atauthor, $id));

    $sql = "DELETE FROM articlestocategories WHERE article_id = ?";
    $app['db']->executeQuery($sql, array($id));

    foreach($atcategories as $rec) {
      $sql = "INSERT INTO articlestocategories(article_id, category_id)
              VALUES(?, ?)";
      $app['db']->executeQuery($sql, array($id, $rec));
    }

    return $app->redirect('../');
  }

}
?>
