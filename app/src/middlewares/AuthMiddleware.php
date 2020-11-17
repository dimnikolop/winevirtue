<?php
namespace app\src\middlewares;
use app\src\Application;
use app\src\exceptions\ForbiddenException;
/**
 * 
 */
class AuthMiddleware extends BaseMiddleware
{
	public $actions = [];
	
	function __construct($actions = [])
	{
		$this->actions = $actions;
	}

	public function execute() {
		if (Application::isGuest()) {
			if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
				throw new ForbiddenException();
			}
		}
	}
}