<?php
namespace app\src;
/**
 * 
 */
final class Database {
	private $dsn = 'mysql:host='.DB_HOST.';port=3306;dbname='.DB_NAME.';charset=utf8mb4';
	private $user = DB_USER;
	private $pass = DB_PASS;
	
	// Single instance of self shared among all instances
	private static $instance = null;
	private $pdo;

	private function __construct()
	{
		// Create database connection
		$this->pdo = new \PDO($this->dsn, $this->user, $this->pass);
		// Set error mode to exception
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}
	
	// Magic method clone is empty to prevent duplication of connection
	private function __clone() {}
	
	private function __wakeup() {}

	/*
	Get an instance of the Database
	@return Instance
	*/
	public static function getInstance()
	{
		if (is_null(self::$instance)) { // If there is no instance then make one
			self::$instance = new Database();
		}
		return self::$instance;
	}

	// Get pdo connection
	public function getConnection()
	{
		return $this->pdo;
	}
}