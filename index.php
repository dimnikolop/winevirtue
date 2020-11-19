<?php
use app\controllers\{SiteController, AuthController};
use app\src\Application;

require_once 'app/config.php'; // Database connection file

require_once __DIR__ . '/vendor/autoload.php';  // Class autoloader file

//Instantiate Application class
$app = new Application();

$app->router->get('/', [SiteController::class, 'home']);

$app->router->get('/about', [SiteController::class, 'about']);

$app->router->get('/articles', [SiteController::class, 'articles']);

$app->router->post('/articles', [SiteController::class, 'articles']);

$app->router->get('/article', [SiteController::class, 'singleArticle']);

$app->router->post('/article', [SiteController::class, 'singleArticle']);

$app->router->get('/wine_reviews', [SiteController::class, 'wineReviews']);

$app->router->post('/wine_reviews', [SiteController::class, 'wineReviews']);

$app->router->get('/wine_review', [SiteController::class, 'singleWine']);

$app->router->get('/contact', [SiteController::class, 'contact']);

$app->router->post('/contact', [SiteController::class, 'contact']);

$app->router->post('/search', [SiteController::class, 'search']);

$app->router->get('/admin/login', [AuthController::class, 'login']);

$app->router->post('/admin/login', [AuthController::class, 'login']);

$app->router->get('/admin/logout', [AuthController::class, 'logout']);

$app->router->get('/admin/dashboard', [AuthController::class, 'dashboard']);

$app->router->get('/admin/post', [AuthController::class, 'postActions']);

$app->router->post('/admin/post', [AuthController::class, 'postActions']);

$app->router->get('/admin/wine', [AuthController::class, 'wineActions']);

$app->router->post('/admin/wine', [AuthController::class, 'wineActions']);

$app->router->get('/admin/posts', [AuthController::class, 'displayPosts']);

$app->router->get('/admin/wines', [AuthController::class, 'displayWines']);

$app->router->get('/admin/users', [AuthController::class, 'users']);

$app->router->post('/admin/users', [AuthController::class, 'users']);


$app->run();