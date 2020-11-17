<?php
namespace app\src\exceptions;
/**
 * 
 */
class NotFoundException extends \Exception
{
	protected $code = 404;
	protected $message = "Page not found";
	
	function __construct()
	{
		# code...
	}
}