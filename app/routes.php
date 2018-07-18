<?php

require_once __DIR__.'/admin.lib.php';
require_once __DIR__.'/news.lib.php';
require_once __DIR__.'/categories.lib.php';

$app->mount('/admin', new NP\AdminControllerProvider());
$app->mount('/', new NP\NewsControllerProvider());
$app->mount('/categories', new NP\CategoriesControllerProvider());

?>
