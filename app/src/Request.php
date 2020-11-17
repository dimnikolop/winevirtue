<?php
namespace app\src;
/**
 * 
 */
class Request
{
	
	function __construct()
	{
		# code...
	}

	public function getPath()
	{
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
		$position = strpos($uri, '?');
		
		if ($position === false) {
			$uri = explode('/', $uri);
			if ($uri[1] == 'admin') {
				for ($i=0; $i <= 2; $i++) {
					$path[] = $uri[$i]; 
				}
			}
			else {
				for ($i=0; $i <= 1; $i++) { 
					$path[] = $uri[$i];
				}
			}
			$path = implode('/', $path);
		}
		else {
			$path = substr($uri, 0, $position);
			$ck_pos = strpos($path, 'ck_upload');
			$ar_pos = strpos($path, 'article/');
			$wi_pos = strpos($path, 'wine_review/');
			if ($ck_pos !== false) {
				$path = substr($path, 0, $ck_pos-1);	
			}
			if ($ar_pos !== false) {
				$path = substr($path, 0, strpos($path, '/', 1));
			}
			if ($wi_pos !== false) {
				$path = substr($path, 0, strpos($path, '/', 1));
			}
			
		}
		return $path;
	}

	public function getMethod()
	{
		return strtolower($_SERVER['REQUEST_METHOD']);
	}

	public function getBody()
	{
		$body = [];
		if($this->getMethod() === 'get') {
			foreach ($_GET as $key => $value) {
				$body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			}
		}
		if($this->getMethod() === 'post') {
			foreach ($_POST as $key => $value) {
				if ($key === 'content') {
					$body[$key] = $value;
				}
				else {
					$body[$key] = filter_var(trim($value), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
				}
			}
		}
		return $body;
	}

	public function getUrlParams()
	{
		$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
		$uri = urldecode($uri);
		$path = $this->getPath();

		$params = mb_substr($uri, strlen($path));
		
		$position = strpos($params, '?');
		
		if ($position !== false) {
			$params = substr($params, 0, $position) . '/';
		}

		if ($params !== '/' && !empty($params)) {
			$params = explode('/', $params);
			array_shift($params);
			
			foreach ($params as $key => $param) {
				$params[$key] = filter_var($param, FILTER_SANITIZE_SPECIAL_CHARS);
			}
		}
		else {
			$params = false;
		}
		
		return $params;
	}
}