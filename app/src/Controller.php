<?php
namespace app\src;
use app\src\Application;
/**
 * 
 */
class Controller
{
	public $layout = 'main';
	public $action = '';
	protected $middlewares = [];

	function __construct()
	{
		# code...
	}

	public function setLayout($layout)
	{
		$this->layout = $layout;
	}

	protected function render($view, $data = [])
	{
		return Application::$app->view->renderView($view, $data);
	}

	public function registerMiddleware($middleware)
	{
		$this->middlewares[] = $middleware;
	}

	public function getMiddlewares()
	{
		return $this->middlewares;
	}
}