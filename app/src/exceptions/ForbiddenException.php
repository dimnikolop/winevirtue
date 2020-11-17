<?php
namespace app\src\exceptions;
/**
 * 
 */
class ForbiddenException extends \Exception
{
	protected $code = 403;
	protected $message = 'You don\'t have permission to access this page';
	
	function __construct()
	{
		# code...
	}
}