<?php
namespace app\src;
use app\models\User;
/**
 * 
 */
class Application {

	public $router;
	public $request;
	public $response;
	public $session;
	public $view;
	public $dbc;
	public $user;
	public static $app;
	public $controller;

	function __construct()
	{	
		self::$app = $this;
		$this->request = new Request();
		$this->response = new Response();
		$this->session = new Session();
		$this->router = new Router($this->request, $this->response);
		$this->view = new View();
		$dbh = Database::getInstance();
		$this->dbc = $dbh->getConnection();

		$primaryValue = $this->session->get('user');
		if($primaryValue) {
			$primaryKey = User::primaryKey();
			$this->user = User::findOne(['id' => $primaryValue], 'obj', ['id', 'fullname', 'email', 'role']);
		}
	}

	public function run() {
		try {
			echo $this->router->resolve();
		}
		catch (\Exception $e) {
			$this->response->setStatusCode($e->getCode());
			echo $this->view->renderView('error', ['exception' => $e]);
		}
	}

	public function setController() {
		$this->controller = $controller;
	}

	public function getController() {
		return $this->controller;
	}

	public function login($user) {
		$this->user = $user;
		$primaryKey = $user->primaryKey();
		$primaryValue = $user->{$primaryKey};
		$this->session->set('user', $primaryValue);
		return true;
	}

	public function logout() {
		$this->user = null;
		$this->session->remove('user');
	}

	public function isAuthorized() {
		return $this->session->get('user');
		//return self::$app->user;
	}

	public static function isGuest() {
		return !self::$app->user;
	}
}