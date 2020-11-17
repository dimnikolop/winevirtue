<?php
namespace app\src;
/**
 * 
 */
class Response
{
	
	function __construct()
	{
		# code...
	}

	public function setStatusCode($code) {
		http_response_code($code);
	}

	public function redirect($url) {
		header('Location: ' . $url);
	}
}