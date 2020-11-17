<?php
namespace app\src\middlewares;
/**
 * 
 */
abstract class BaseMiddleware
{
	
	function __construct()
	{
		# code...
	}

	abstract public function execute();
}