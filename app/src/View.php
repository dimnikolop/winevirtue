<?php
namespace app\src;
use app\src\Application;
/**
 * 
 */
class View
{
	public $title = '';
	public $pageDescription = '';
	public $url = '';
	public $ogType = 'website';
	public $ogDescription = '';
	public $ogImage = '';

	function __construct()
	{
		# code...
	}

	public function renderView($view, $params = [])
	{
		if ($view == 'login') {
			return include_once ROOT_PATH . "app/views/$view.phtml";
			exit;
		}
		$viewContent = (!empty($params)) ? $this->renderOnlyview($view, $params) : $this->renderOnlyview($view);
		$layoutContent = $this->layoutContent();
		return str_replace('{{content}}', $viewContent, $layoutContent);
	}

	public function renderContent($viewContent)
	{
		$layoutContent = $this->layoutContent();
		return str_replace('{{content}}', $viewContent, $layoutContent);
	}

	protected function layoutContent()
	{
		$layout = (Application::$app->controller->layout) ?? 'main';
		ob_start();
		include_once ROOT_PATH . "app/views/layouts/$layout.phtml";
		return ob_get_clean();
	}

	protected function renderOnlyview($view, $params = [])
	{
		/*foreach ($params as $key => $param) {
			$$key = $param;
		}*/
		ob_start();
		include_once ROOT_PATH . "app/views/$view.phtml";
		return ob_get_clean();
	}
}