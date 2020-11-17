<?php
namespace app\controllers;
use app\src\{Controller, Application};
use app\src\middlewares\{AuthMiddleware, NotFoundException};
use app\models\{LoginForm, Article, Wine, User};
/**
 * 
 */
class AuthController extends Controller
{
	
	function __construct()
	{
		//	Restrict access to admin panel - Only logged in users(Admin, Authors) can use these functions/pages
		$this->registerMiddleware(new AuthMiddleware(['dashboard', 'postActions', 'wineActions', 'displayPosts', 'displayWines', 'users']));
	}

	public function dashboard()
	{
		$this->setLayout('auth');
		return $this->render('dashboard');
	}

	public function login($request, $response)
	{
		$loginForm = new LoginForm();

		if ($request->getMethod() === 'post') {
			$loginForm->loadData($request->getBody());
			
			if ($loginForm->validate() && $loginForm->login()) {
				$response->redirect('/admin/dashboard');
				exit;
			}
		}
		return $this->render('login', ['model' => $loginForm]);
	}

	public function postActions($request)
	{
		$article = new Article();
		$params = $request->getUrlParams();

		if ($params) {
			switch ($params[0]) {
				case 'edit':
					$article = $article->edit($params[1]);
					break;
				case 'ck_upload':
					$article = $article->ckUpload();
					break;
				case 'delete':
					if ($article->delete($params[1])) {
						Application::$app->session->setFlash('success', 'Post successfully deleted');
						Application::$app->response->redirect('/admin/posts');
						exit;
					}
					else {
						Application::$app->session->setFlash('error', 'Failed to delete post');
						Application::$app->response->redirect('/admin/posts');
						exit;
					}
					break;
				case 'publish':
					if ($article->togglePublishPost($params[1])) {
						Application::$app->session->setFlash('success', 'Post published successfully');
						Application::$app->response->redirect('/admin/posts');
						exit;
					}
					break;
				case 'unpublish':
					if ($article->togglePublishPost($params[1])) {
						Application::$app->session->setFlash('success', 'Post successfully unpublished');
						Application::$app->response->redirect('/admin/posts');
						exit;
					}
					break;
				default:
					throw new NotFoundException();
					break;
			}
		}

		if ($request->getMethod() === 'post') {
			$clean_data = $request->getBody();
			$article->loadData($clean_data);
			
			if (isset($clean_data['create_post'])) {
				if($article->validate() && $article->create()) {
					Application::$app->session->setFlash('success', 'Post created successfully');
					Application::$app->response->redirect('/admin/posts');
					exit;
				}
			}
			elseif (isset($clean_data['update_post'])) {
				if($article->validate('update') && $article->update()) {
					Application::$app->session->setFlash('success', 'Post updated successfully');
					Application::$app->response->redirect('/admin/posts');
					exit;
				}
			}	
		}

		$this->setLayout('auth');
		// load the view and pass the article and flag(isEditingPost)
		return $this->render('add_post', ['model' => $article, 'isEditingPost' => Article::$isEditingPost]);
	}

	public function wineActions($request)
	{
		$wine = new Wine();
		$params = $request->getUrlParams();

		if ($params) {
			switch ($params[0]) {
				case 'edit':
					$wine = $wine->edit($params[1]);
					break;
				case 'delete':
					if ($wine->delete($params[1])) {
						Application::$app->session->setFlash('success', 'Wine successfully deleted');
						Application::$app->response->redirect('/admin/wines');
						exit;
					}
					else {
						Application::$app->session->setFlash('error', 'Failed to delete wine review');
						Application::$app->response->redirect('/admin/wines');
						exit;
					}
					break;
				case 'publish':
					if ($wine->togglePublishWine($params[1])) {
						Application::$app->session->setFlash('success', 'Wine review published successfully');
						Application::$app->response->redirect('/admin/wines');
						exit;
					}
					break;
				case 'unpublish':
					if ($wine->togglePublishWine($params[1])) {
						Application::$app->session->setFlash('success', 'Wine review successfully unpublished');
						Application::$app->response->redirect('/admin/wines');
						exit;
					}
					break;
				default:
					throw new NotFoundException();
					break;
			}
		}

		if ($request->getMethod() === 'post') {
			$clean_data = $request->getBody();
			$wine->loadData($clean_data);

			if (isset($clean_data['create_wine'])) {
				if($wine->validate() && $wine->create()) {
					Application::$app->session->setFlash('success', 'Wine created successfully');
					Application::$app->response->redirect('/admin/wines');
					exit;
				}
			}
			elseif (isset($clean_data['update_wine'])) {
				if($wine->validate('update') && $wine->update()) {
					Application::$app->session->setFlash('success', 'Wine updated successfully');
					Application::$app->response->redirect('/admin/wines');
					exit;
				}
			}	
		}

		$this->setLayout('auth');
		// Load the view and pass the wine and flag(isEditingWine)
		return $this->render('add_wine', ['model' => $wine, 'isEditingWine' => Wine::$isEditingWine]);
	}

	// Display articles table
	public function displayPosts($request)
	{
		if ($request->getUrlParams()) {
			throw new NotFoundException();
		}
		$article = new Article();
		$posts = $article->getAllPosts();

		$this->setLayout('auth');
		// Load the view and pass the posts as parameter
		return $this->render('posts', ['model' => $posts]);
	}

	// Display wines table
	public function displayWines($request)
	{	
		if ($request->getUrlParams()) {
			throw new NotFoundException();
		}
		$wine = new Wine();
		$wines = $wine->getAllWines();

		$this->setLayout('auth');
		// Load the view and pass the wines as parameter
		return $this->render('wines', ['model' => $wines]);
	}

	public function users($request)
	{
		$user = new User();
		$params = $request->getUrlParams();

		if ($params) {
			switch ($params[0]) {
				case 'edit':
					$user = $user->edit($params[1]);
					break;
				case 'delete':
					if ($user->delete($params[1])) {
						Application::$app->session->setFlash('success', 'User successfully deleted');
						Application::$app->response->redirect('/admin/users');
						exit;
					}
					else {
						Application::$app->session->setFlash('error', 'Failed to delete user');
						Application::$app->response->redirect('/admin/users');
						exit;
					}
					break;
				default:
					throw new NotFoundException();
					break;
			}
		}

		if($request->getMethod() === 'post') {
			$clean_data = $request->getBody();
			$user->loadData($clean_data);

			if (isset($clean_data['create'])) {
				if ($user->validate() && $user->create()) {
					Application::$app->session->setFlash('success', 'User created successfully - Thanks for registering!');
					Application::$app->response->redirect('/admin/users');
					exit;
				}
			}
			elseif (isset($clean_data['update'])) {
				if ($user->validate('update') && $user->update()) {	
					Application::$app->session->setFlash('success', 'User updated successfully!');
					Application::$app->response->redirect('/admin/users');
					exit;
				}
			}	
		}
		// Get all registered users from database
		$users = $user->getAdminUsers();

		$this->setLayout('auth');
		// Load the view and pass as parameters the users, the current user if editing and the flag(isEditingUser) 
		return $this->render('users', ['model' => $user, 'users' => $users, 'isEditingUser' => User::$isEditingUser]);
	}

	public function logout($request, $response)
	{
		Application::$app->logout();
		$response->redirect('/admin/login');
	}
}